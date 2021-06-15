<?php

namespace PDNS\DNS;

class Registry
{
    /**
     * @var array
     * [
     *      "label" => [
     *          "container" => [
     *              "network1" => "cidr",
     *              "network2" => "cidr",
     *          ]
     *      ]
     * ]
     */
    protected array $entries = [];

    public function __construct(
        protected string $tld = 'docker',
    ) { }

    public function removeDockerContainer(string $id) : void
    {
        foreach ($this->entries as $domain => & $containers) {
            foreach ($containers as $containerId => $unused) {
                if ($id === $containerId) {
                    unset($containers[$containerId]);
                }
            }
            if (empty($containers)) {
                unset($this->entries[$domain]);
            }
        }
    }

    /**
     * domains generation
     *
     * docker-compose:
     * 1.service.project.tld
     *   service.project.tld
     *
     * single network:
     * aliases
     * - nodots: alias.network_name.docker
     * -   dots: alias
     */
    public function appendDockerContainer(string $id, array $labels, array $networks)
    {
        $addresses  = array_map(fn($network) => $network['IPAddress'] ?? null, $networks);
        $addresses  = array_filter($addresses);

        $composeProject = $composeService = $composeContainer = null;

        foreach ($labels as $labelName => $labelValue) {
            match ($labelName) {
                'com.docker.compose.project'          => $composeProject    = $labelValue,
                'com.docker.compose.service'          => $composeService    = $labelValue,
                'com.docker.compose.container-number' => $composeContainer  = $labelValue,
                default => null,
            };
        }

        if (!is_null($composeProject) && !is_null($composeService) && count($addresses)) {
            $label = "{$composeService}.{$composeProject}.{$this->tld}";

            $this->addRecords($label, $id, $addresses);

            if (!is_null($composeContainer)) {
                $label = "{$composeContainer}.{$composeService}.{$composeProject}.{$this->tld}";

                $this->addRecords($label, $id, $addresses);
            }
        }

        foreach ($networks as $networkName => $network) {
            $label = "{$networkName}.{$this->tld}";

            if ($ip = ($network['IPAddress'] ?? null)) {
                foreach ($network['Aliases'] ?? [] as $alias) {
                    if (str_contains($alias, '.')) {
                        $this->addRecords($alias, $id, [$networkName => $ip]);
                    } else {
                        $this->addRecords("{$alias}.{$label}", $id, [$networkName => $ip]);
                    }
                }
            }
        }
    }

    private function addRecords(string $label, string $id, array $addresses)
    {
        $this->entries[$label]      = $this->entries[$label] ?? [];
        $this->entries[$label][$id] = $this->entries[$label][$id] ?? [];

        foreach ($addresses as $network => $address) {
            $this->entries[$label][$id][$network] = $address;
        }
    }
}
