<?php

/**
 * @Author: Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Creation time: 2017-06-10 06:58:53
 * @Last modified time: 2017-06-10 10:17:23
 * @link: http://www.giaphugroup.com
 *
 */

namespace PHPCuong\FacebookAutoPost\Model\Source;

class FacebookPages
{
    /**
     * Get the facebook settings
     *
     * @var \PHPCuong\FacebookAutoPost\Model\Source\FacebookSettings
     */
    protected $facebookSettings;

    /**
     *
     * @var \PHPCuong\FacebookAutoPost\Model\Source\RemoteUrl
     */
    protected $remoteUrl;

    public function __construct(
        \PHPCuong\FacebookAutoPost\Model\Source\FacebookSettings $facebookSettings,
        \PHPCuong\FacebookAutoPost\Model\Source\RemoteUrl $remoteUrl
    ) {
        $this->facebookSettings = $facebookSettings;
        $this->remoteUrl = $remoteUrl;
    }
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /**
         * making filter by allowed pages
         */
        $pages = $this->getAllowedPages();
        $options = [];

        foreach ($pages as $page) {
            $options[] = ['value' => $page->id, 'label' => $page->name];
        }

        return $options;
    }

    protected function getAllowedPages()
    {
        $app_id = $this->facebookSettings->getFacebookSetting('catalog/facebook_auto_post/application_id');
        $app_secret = $this->facebookSettings->getFacebookSetting('catalog/facebook_auto_post/application_secret');
        $access_token = 'EAAQJ3VSq79YBAIW1triazViJ3P5Syd9PODvDDi0aMhkvdcA8GTo8y29EYITmSQwXgK6zN2bMmggTXjdUp3WknJLVvmpACknWa58nI5cYn6QlZBCCJkeZAvWE9XRzE5LxkedSZBDAWOvICEEQtmNXq0DLj1PtWn3mzSEgjgLxAZDZD';
        $rs = json_decode($this->remoteUrl->getFacebookPagesListByToken($access_token));
        if (is_array($rs->data)) {
            return $rs->data;
        }
        return [];
    }
}
