<?php

namespace Crm\SubscriptionsModule\Report;

use Kdyby\Translation\Translator;
use Nette\Database\Context;

abstract class BaseReport implements ReportInterface
{
    private $name;

    private $id;

    /** @var  Context */
    private $db;

    protected $translator;

    public function __construct($name, Translator $translator)
    {
        $this->name = $name;
        $this->id = md5(time() . rand(1, 10000) . rand(1000, 1000) . 'hello');
        $this->translator = $translator;
    }

    protected function getDatabase()
    {
        return $this->db;
    }

    public function getName()
    {
        return $this->name;
    }

    public function injectDatabase(Context $db)
    {
        $this->db = $db;
    }

    public function getId()
    {
        return $this->id;
    }
}
