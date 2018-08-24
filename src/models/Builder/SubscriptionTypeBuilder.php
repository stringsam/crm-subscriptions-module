<?php

namespace Crm\SubscriptionsModule\Builder;

use Crm\ApplicationModule\Builder\Builder;
use Nette\Database\Table\IRow;

class SubscriptionTypeBuilder extends Builder
{
    protected $tableName = 'subscription_types';

    private $magazinesSubscriptionTypesTable = 'subscription_type_magazines';

    private $magazines = [];

    public function isValid()
    {
        if (strlen($this->get('name')) < 1) {
            $this->addError('Nebolo zadané meno');
            return false;
        }
        if (!$this->exists('price')) {
            $this->addError('Nebola zadaná cena');
        }

        if (count($this->getErrors()) > 0) {
            return false;
        }
        return true;
    }

    protected function setDefaults()
    {
        parent::setDefaults();
        $this->set('created_at', new \DateTime());
        $this->set('modified_at', new \DateTime());
        $this->set('disable_notifications', false);
        $this->set('length_method_id', 'fix_days');
    }

    public function setName($name)
    {
        return $this->set('name', $name);
    }

    public function setCode($code)
    {
        return $this->set('code', $code);
    }

    public function setDescription($description)
    {
        return $this->set('description', $description);
    }

    public function setPrice($price)
    {
        return $this->set('price', $price);
    }

    public function setLength($length)
    {
        return $this->set('length', $length);
    }

    public function setFixedEnd($fixedEnd)
    {
        if ($fixedEnd) {
            return $this->set('fixed_end', $fixedEnd);
        }
        return $this;
    }

    public function setExtendingLength($extendingLength)
    {
        return $this->set('extending_length', $extendingLength);
    }

    public function setActive($active)
    {
        return $this->set('active', $active);
    }

    public function setVisible($visible)
    {
        return $this->set('visible', $visible);
    }

    public function setWeb($web)
    {
        return $this->set('web', $web);
    }

    public function setPrint($print)
    {
        return $this->set('print', $print);
    }

    public function setPrintFriday($print_friday)
    {
        return $this->set('print_friday', $print_friday);
    }

    public function setAdFree($adFree)
    {
        return $this->set('ad_free', $adFree);
    }

    public function setClub($club)
    {
        return $this->set('club', $club);
    }

    public function setMobile($mobile)
    {
        return $this->set('mobile', $mobile);
    }

    public function setDefault($default)
    {
        return $this->set('default', $default);
    }

    public function setSorting($sorting)
    {
        return $this->set('sorting', $sorting);
    }

    public function setNoSubscription($boolean)
    {
        return $this->set('no_subscription', $boolean);
    }

    public function setNextSubscriptionTypeId($id)
    {
        return $this->set('next_subscription_type_id', $id);
    }

    public function setUserLabel($userLabel)
    {
        return $this->set('user_label', $userLabel);
    }

    public function setType($type)
    {
        return $this->set('type', $type);
    }

    public function setLimitPerUser($limitPerUser)
    {
        return $this->set('limit_per_user', $limitPerUser);
    }

    public function setAskAddress($askAddress)
    {
        return $this->set('ask_address', $askAddress);
    }

    public function setExtensionMethod($extensionMethod)
    {
        return $this->set('extension_method_id', $extensionMethod);
    }

    public function setLengthMethod($lengthMethod)
    {
        return $this->set('length_method_id', $lengthMethod);
    }

    public function setDisabledNotifications($disableNotifications)
    {
        return $this->set('disable_notifications', $disableNotifications);
    }

    public function setRecurrentChargeBefore($recurrentChargeBefore)
    {
        return $this->set('recurrent_charge_before', $recurrentChargeBefore);
    }

    public function addMagazine(IRow $row)
    {
        $this->magazines[] = $row;
        return $this;
    }

    public function setContentAccess($contentAccess)
    {
        foreach ($contentAccess as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    protected function store($tableName)
    {
        $subscriptionType = parent::store($tableName);
        foreach ($this->magazines as $magazine) {
            $exists = $this->database->table($this->magazinesSubscriptionTypesTable)->where(['magazine_id' => $magazine->id, 'subscription_type_id' => $subscriptionType->id])->count('*') > 0;
            if (!$exists) {
                $this->database->table($this->magazinesSubscriptionTypesTable)
                    ->insert(['magazine_id' => $magazine->id, 'subscription_type_id' => $subscriptionType->id]);
            }
        }
        return $subscriptionType;
    }
}
