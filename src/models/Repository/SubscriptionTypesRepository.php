<?php

namespace Crm\SubscriptionsModule\Repository;

use Crm\ApplicationModule\Repository;
use Crm\ApplicationModule\Repository\AuditLogRepository;
use Nette\Database\Context;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

class SubscriptionTypesRepository extends Repository
{
    protected $tableName = 'subscription_types';

    const TYPE_TIME_ARCHIVE = 'time_archive';
    const TYPE_TIME = 'time';
    const TYPE_PRODUCT = 'product';

    public function __construct(Context $database, AuditLogRepository $auditLogRepository)
    {
        parent::__construct($database);
        $this->auditLogRepository = $auditLogRepository;
    }

    public function getAllActive()
    {
        return $this->getTable()->where(['active' => true])->order('sorting');
    }

    public function getAllVisible()
    {
        return $this->getTable()->where(['active' => true, 'visible' => true])->order('sorting');
    }

    /**
     * @return Selection
     */
    public function all()
    {
        return $this->getTable()->order('sorting');
    }

    public function update(IRow &$row, $data)
    {
        $values['modified_at'] = new \DateTime();
        return parent::update($row, $data);
    }

    public function getPrintSubscriptionTypes()
    {
        return $this->getTable()->where(['print' => 1])->fetch();
    }

    public function exists($code)
    {
        return $this->getTable()->where('code', $code)->count('*') > 0;
    }

    public function findByCode($code)
    {
        return $this->getTable()->where('code', $code)->fetch();
    }

    public function availableTypes()
    {
        return [
            self::TYPE_TIME_ARCHIVE => self::TYPE_TIME_ARCHIVE,
            self::TYPE_TIME => self::TYPE_TIME,
            self::TYPE_PRODUCT => self::TYPE_PRODUCT,
        ];
    }
}
