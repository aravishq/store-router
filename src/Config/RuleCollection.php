<?php

namespace Alpaca\StoreRouter\Config;

use Alpaca\StoreRouter\Rule\BasicAuth;
use Alpaca\StoreRouter\Rule\MageRunCode;
use Alpaca\StoreRouter\Rule\Robots;
use Alpaca\StoreRouter\Rule\Sitemap;

class RuleCollection
{

    /**
     * @var array
     */
    protected static $ruleMapping = [
        'mage_run_code'     => MageRunCode::class,
        'auth'              => BasicAuth::class,
        'robots'            => Robots::class,
        'sitemap'           => Sitemap::class
    ];

    /**
     * @var array
     */
    protected $rules;

    /**
     * @param array $rules
     */
    public function __construct(
        array $rules
    ) {
        $this->rules = $rules;
    }

    /**
     * @param \Alpaca\StoreRouter\Config\RuleCollection $rules
     *
     * @return self
     */
    public function merge(RuleCollection $rules)
    {
        $this->rules = array_merge_recursive($this->rules, $rules->getRules());

        return $this;
    }

    /**
     * @param string|null $groupId
     *
     * @return void
     */
    public function assert(?string $groupId = null)
    {
        foreach ($this->rules as $key => $value) {
            $className = self::$ruleMapping[$key] ?: null;

            if (empty($className) || !class_exists($className)) {
                continue;
            }

            /** @var \Alpaca\StoreRouter\Rule\RuleContract|\Alpaca\StoreRouter\Rule\Rule $ruleObject */
            $ruleObject = new $className();

            if (!empty($groupId)) {
                $ruleObject->setGroupId($groupId);
            }

            $ruleObject->assert($value);
        }
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
