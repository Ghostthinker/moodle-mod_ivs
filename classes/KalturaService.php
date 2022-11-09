<?php

class KalturaService {
    public $client = null;

    public function __construct() {
        global $USER, $CFG;

        if (!file_exists($CFG->dirroot . '/local/kaltura/API/KalturaClient.php')) {
            throw new \Exception('This bundle needs the kaltura package to be installed.');
        }

        require_once($CFG->dirroot . '/local/kaltura/API/KalturaClient.php');
        require_once($CFG->dirroot . '/local/kaltura/locallib.php');


        $configsettings = get_config(KALTURA_PLUGIN_NAME);
        $secret = $configsettings->adminsecret;
        $userid = $USER->id;
        $type = KalturaSessionType::ADMIN;
        $partnerid = $configsettings->partner_id;
        $expiry = 86400;
        $privileges = "";

        $config = new KalturaConfiguration($partnerid);
        $config->serviceUrl = $configsettings->uri;
        $this->client = new KalturaClient($config);

        $ks = $this->client->session->start($secret, $userid, $type, $partnerid, $expiry, $privileges);
        $this->client->setKs($ks);
    }

    /**
     * get list of available media in course context
     * @param $courseid
     * @return array
     */
    public function getMediaList($courseid) {
        $medialist = [];
        try {

            $filter = new KalturaMediaEntryFilter();
            $pager = new KalturaFilterPager();
            $pager->pageSize = 100;

            $filter->categoriesMatchOr = "Moodle>site>channels>" . $courseid;

            try {
                $medialist = $this->client->media->listAction($filter, $pager);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $medialist;
    }

    /**
     * get kaltura media dataurl
     * @param $entityid
     * @return null
     */
    public function getMediaDataUrl($entityid) {
        $dataurl = null;
        try {
            $entry = $this->client->media->get($entityid);
            $dataurl = $entry->dataUrl;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $dataurl;
    }
}
