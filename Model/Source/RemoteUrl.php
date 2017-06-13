<?php

/**
 * @Author: Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Creation time: 2017-06-10 08:21:34
 * @Last modified time: 2017-06-10 09:13:51
 * @link: http://www.giaphugroup.com
 *
 */

namespace PHPCuong\FacebookAutoPost\Model\Source;

use Zend\Http\Client;
use Zend\Http\Request;

class RemoteUrl
{
    /**
     * @param string $ip
     * @return string
     */
    public function getFacebookPagesListByToken($token_access)
    {
        $request = new Request();
        $request->setUri('https://graph.facebook.com/v2.8/me/accounts?access_token='.$token_access.'&limit=10000&offset=0');
        $client = new Client();
        $client->setMethod('GET');
        $response = $client->send($request);
        if ($response->isSuccess()) {
            return $response->getBody();
        }
        return '';
    }
}
