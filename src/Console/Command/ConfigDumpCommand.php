<?php declare(strict_types=1);

namespace Alpaca\StoreRouter\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigDumpCommand extends Command
{
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected StoreRepositoryInterface $storeRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface        $storeRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string|null                                        $name
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        ScopeConfigInterface $scopeConfig,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->storeRepository = $storeRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('store-router:config:dump');
        $this->setDescription('Dump configuration for store router to yaml format.');

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $baseUrl = $this->getStoreBaseUrl($store);

            $config[$store->getCode()] = [
                'rules' => ['mage_run_code' => $store->getCode()],
                'conditions' => ['hosts' => [$baseUrl => '*']]
            ];
        }

        if (empty($config)) {
            $output->writeln("No config available to dump.");

            return 1;
        }

        $output->writeln(Yaml::dump($config, 4));

        return 0;
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return string
     */
    protected function getStoreBaseUrl(StoreInterface $store): string
    {
        $baseUrl = $this->scopeConfig->getValue(
            'web/secure/base_url',
            ScopeInterface::SCOPE_STORES,
            $store->getId()
        );

        return rtrim(preg_replace('/https?:\/\//', '', $baseUrl), '/');
    }
}
