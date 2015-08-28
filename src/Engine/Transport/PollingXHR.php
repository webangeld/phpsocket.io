<?php
namespace Engine\Transport;
class PollingXHR extends Polling
{
    public function onRequest($req)
    {
        if('OPTIONS' === $req->method)
        {
            $res = $req->res;
            $headers = $this->headers($req);
            $headers['Access-Control-Allow-Headers'] = 'Content-Type';
            $res->writeHead(200, '', $headers);
            $res->end();
        } 
        else
        {
            parent::onRequest($req);
        }
    }

    public function doWrite($data)
    {
        // explicit UTF-8 is required for pages not served under utf todo
        //$content_type = $isString
        //    ? 'text/plain; charset=UTF-8'
        //    : 'application/octet-stream';
        $content_type = 'application/octet-stream';
        $content_length = strlen($data);
        $headers = array( 
            'Content-Type'=> $content_type,
            'Content-Length'=> $content_length
        );

        // prevent XSS warnings on IE
        // https://github.com/LearnBoost/socket.io/pull/1333
        $ua = $this->req->headers['user-agent'];
        if ($ua && (strpos($ua, ';MSIE') || strpos($ua, 'Trident/'))) 
        {
            $headers['X-XSS-Protection'] = '0';
        }
        $this->res->writeHead(200, '', $this->headers($this->req, $headers));
        $this->res->end($data);
    }
    
    public function headers($req, $headers = array())
    {
       if($req->headers['origin']) 
       {
           $headers['Access-Control-Allow-Credentials'] = 'true';
           $headers['Access-Control-Allow-Origin'] = $req->headers['origin'];
       } 
       else
       {
           $headers['Access-Control-Allow-Origin'] = '*';
       }
       $listeners = $this->listeners('headers');
       foreach($listeners as $listener)
       {
           $listener($headers);
       }
       return $headers;
    }
 

}
