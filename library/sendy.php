<?php

/**
 * @package         Sendy Library - Implments subscribe and unsubscribe functions
 * @copyright (c)   2014 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v1.0
 * @build-date      2014-03-05
 */
class Sendy
{
    var $apiKey;
    var $params;

    public function __construct()
    {
        jimport('joomla.plugin.helper');
        $this->params = new JRegistry();
        $plugin = JPluginHelper::getPlugin('system', 'sendy');
        if ($plugin)
        {
            $this->params->loadString($plugin->params);
        }
    }

    // Really just a helper to prevent
    static $instance;

    public static function getInstance()
    {
        if (!self::$instance)
        {
            self::$instance = new Sendy();
        }
        return self::$instance;
    }

    public function __get($name)
    {
        switch ($name)
        {
            case 'sendyUrl':
                return $this->params->get('sendy_url');
            case 'subscribeUrl':
                return $this->sendyUrl . '/subscribe';
            case 'unsubscribeUrl':
                return $this->sendyUrl . '/unsubscribe';
        }
    }

    public function subscribeUser($userName, $email, $listId, $apiKey = '')
    {
        $list = null;
        $lists = $this->getLists();
        if (isset($lists[$listId]))
            $list = $lists[$listId];

        if ($list)
        {
            $http = JHttpFactory::getHttp();
            $data = array();
            $data['api_key'] = $apiKey;
            $data['name'] = $userName;
            $data['email'] = $email;
            $data['list'] = $list->id;
            $data['boolean'] = 'true';

            $return = $http->post($this->subscribeUrl, $data);
            $return = $return->body;
        }
        else
            $return = "That list is not defined";

        return $return;
    }

    public function unsubscribeUser($userName, $email, $listId)
    {
        $list = null;

        $lists = $this->getLists();
        if (isset($lists[$listId]))
            $list = $lists[$listId];

        if ($list)
        {
            $http = JHttpFactory::getHttp();
            $data = array();
            $data['name'] = $userName;
            $data['email'] = $email;
            $data['list'] = $list->id;
            $data['boolean'] = 'true';

            $return = $http->post($this->unsubscribeUrl, $data);
            $return = $return->body;
        }
        else
            $return = "That list is not defined";

        return $return;
    }

    public function getLists()
    {
        $lists = array();

        $options[] = JHtml::_('select.option', "--", "-- Select a Sendy List --");
        for ($i = 1; $i < 20; $i++)
        {
            if ($this->params->get('list' . $i . '_id') != null)
            {
                $list = new stdClass();
                $list->name = $this->params->get('list' . $i . '_name');
                $list->id = $this->params->get('list' . $i . '_id');
                $lists[$i] = $list;
            }
        }
        return $lists;
    }
}