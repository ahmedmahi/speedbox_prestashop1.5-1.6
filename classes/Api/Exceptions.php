<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class S3ibusiness_Speedbox_Model_Api_Exceptions extends Exception
{

    protected $request;

    protected $response;

    public function __construct($message, $code = 0, $request = '', $response = '')
    {

        parent::__construct($message, $code);

        $this->request  = $request;
        $this->response = $response;
    }

    public function get_request()
    {

        return $this->request;
    }

    public function get_response()
    {

        return $this->response;
    }

}
