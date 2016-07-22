<?php

namespace EXS\NormalizedUrlBundle\Services;

use Doctrine\ORM\EntityManager;
use EXS\NormalizedUrlBundle\Entity\Url;
use EXS\NormalizedUrlBundle\Entity\Host;
use EXS\NormalizedUrlBundle\Entity\Path;
use EXS\NormalizedUrlBundle\Entity\Querystring;

class UrlManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Initiate the service
     * 
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Process url service
     * Search/Create/get url then return id.
     * 
     * @param obj $refData
     * @return int
     */
    public function getUrl($refData)
    {
        $urlId = null;
        if(isset($refData->url) && !empty($refData->url)) { 
            $urlHash = $this->generateUrlHash($refData->url);       
            $url = $this->getUrlByHash($urlHash);
            $urlId = $this->processUrl($url, $urlHash, $refData);
        }
        return $urlId;
    }
    
    /**
     * Generate/Get url id
     * 
     * @param mixed $url
     * @param string $urlHash
     * @param object $refData
     * @return int
     */
    public function processUrl($url, $urlHash, $refData)
    {
        if($url === false) {
            $host = $this->processHost($refData->host);
            $path = $this->processPath($refData->path);
            $querystring = $this->processQuerystring($refData->qs);               
            $url = $this->createUrl($urlHash, $host, $path, $querystring);
        }
        return $url->getId();        
    }
    
    /**
     * Add new url
     * 
     * @param string $urlHash
     * @param obj $host
     * @param obj $path
     * @param obj $querystring
     * @return Url
     */
    public function createUrl($urlHash, $host, $path, $querystring)
    {       
        $url = new Url();
        $url->setHashkey($urlHash);
        $url->setHost($host);
        $url->setPath($path);
        $url->setQuerystring($querystring);
        $url->setLogged(new \DateTime());
        $this->entityManager->persist($url);
        $this->entityManager->flush(); 
        return $url;
    }
    
    /**
     * Search/Create/Get Host
     * 
     * @param string $hostName
     * @return Host
     */
    public function processHost($hostName)
    {
        $host = $this->getHost($hostName);
        if($host === false) {
            return $this->createHost($hostName);
        }
        return $host;
    }
    
    /**
     * Add new host
     * 
     * @param string $hostName
     * @return Host
     */
    public function createHost($hostName)
    {
        if(!empty($hostName)) {
            $host = new Host();
            $host->setName($hostName);
            $host->setLogged(new \DateTime());
            $this->entityManager->persist($host);
            $this->entityManager->flush(); 
            return $host;
        }        
        return null;
    }

    /**
     * Search/Create/Get path
     * 
     * @param string $pathName
     * @return Path
     */
    public function processPath($pathName)
    {
        $path = $this->getPath($pathName);
        if($path === false) {
            $path = $this->createPath($pathName);         
        }
        return $path;
    }
    
    /**
     * Added new path
     * 
     * @param string $pathName
     * @return Path
     */
    public function createPath($pathName)
    {
        if(!empty($pathName)) {
            $path = new Path();
            $path->setName($pathName);
            $path->setLogged(new \DateTime());
            $this->entityManager->persist($path);
            $this->entityManager->flush();   
            return $path;
        }
        return null;
    }    
    
    /**
     * Search/Create/Get querystring
     * 
     * @param string $qsName
     * @return Querystring
     */
    public function processQuerystring($qsName)
    {
        $querystring = $this->getQuerystring($qsName);
        if($querystring === false) {
            $querystring = $this->createQuerystring($qsName);    
        }
        return $querystring;
    }    
    
    /**
     * Add new querystring
     * 
     * @param string $qsName
     * @return Querystring
     */
    public function createQuerystring($qsName)
    {
        if(!empty($qsName)) {
            $querystring = new Querystring();
            $querystring->setName($qsName);
            $querystring->setLogged(new \DateTime());
            $this->entityManager->persist($querystring);
            $this->entityManager->flush(); 
            return $querystring;
        }
        return null;
    }
    
    /**
     * Generate url hash
     * 
     * @param string $urlString
     * @return string
     */
    public function generateUrlHash($urlString)
    {    
        if(strlen($urlString) > 255) {
            $urlString = substr($urlString, 0, 255); // truncate url up to 255 charaters.
        }
        return md5($urlString);        
    }
    
    /**
     * Get url by hash
     * 
     * @param string $urlHash
     * @return Url
     */
    public function getUrlByHash($urlHash)
    {
        $query = $this->entityManager->getRepository('EXSNormalizedUrlBundle:Url')->getUrlByHash($urlHash);      
        $url = $query->getResult();
        if (isset($url[0])) {
            return $url[0];
        }
        return false;
    }    
    
    /**
     * Get host by name
     * 
     * @param string $hostName
     * @return Host
     */
    public function getHost($hostName)
    {
        $query = $this->entityManager->getRepository('EXSNormalizedUrlBundle:Host')->getHostByName($hostName);      
        $host = $query->getResult();
        if (isset($host[0])) {
            return $host[0];
        }
        return false;        
    }
    
    /**
     * Get path by name
     * 
     * @param string $pathName
     * @return Path
     */
    public function getPath($pathName)
    {
        $query = $this->entityManager->getRepository('EXSNormalizedUrlBundle:Path')->getPathByName($pathName);      
        $path = $query->getResult();
        if (isset($path[0])) {
            return $path[0];
        }
        return false;        
    }    
    
    /**
     * Get querystring by name
     * 
     * @param string $qsName
     * @return Querystring
     */
    public function getQuerystring($qsName)
    {
        $query = $this->entityManager->getRepository('EXSNormalizedUrlBundle:Querystring')->getQuerystringByName($qsName);      
        $querystring = $query->getResult();
        if (isset($querystring[0])) {
            return $querystring[0];
        }
        return false;        
    }      
}
