<?php

require_once('../app/framework/Cr/Loader.php');
$loader = new Cr_Loader('../app/framework/');

require_once('../app/framework/simpletest/autorun.php');

class TestFileCache extends UnitTestCase
{	
	public function __construct()
	{
		$this->cache = new Cr_Cache_File(array('path' => './data/', 'time' => array('seconds' => '1')));
		parent::__construct('File Cache Test');
	}
	
	public function testSimpleCache()
	{	
		$cache = $this->cache;
		$cache->clean();
		
		$data = array(
			'time' => time()
		);
		
		$test_name = __FUNCTION__;
		
		$cache->set($test_name, $data);
		$data_cache = $cache->get($test_name);

		$this->assertTrue($data_cache);
		$this->assertIdentical($data, $data_cache);
		
		sleep(1);
		
		$data_cache = $cache->get($test_name);
		$this->assertFalse($data_cache);
	}
	
	public function testTagsCache()
	{
		$cache = $this->cache;
		
		$data = array(
			'time' => time()
		);
		
		$test_name = __FUNCTION__;
		
		$tag_name_dynamic = 'tag_' . time();
		$tags = array('tag_name', $tag_name_dynamic);
		$tags2 = array($tag_name_dynamic, 'tag_name_2');
		$cache->set($test_name, $data, array('tags' => $tags));
		$cache->set($test_name . '_2', $data, array('tags' => $tags2));
				
		$data_cache = $cache->get($test_name);
		$this->assertIdentical($data_cache, $data);
		
		$cache->deleteByTag($tag_name_dynamic);
		$data_cache = $cache->get($test_name);
		$this->assertFalse($data_cache);
	}
	
	public function testCleanCache()
	{
		$cache = $this->cache;
		
		$data = array(
			'time' => time()
		);
		
		$test_name = __FUNCTION__;
		
		$cache->set($test_name, $data, array('tags' => array('tag', 'tag_2')));
		
		sleep(1);
		
		$cache->clean();
		$files = glob('./data/*.cache');
		$this->assertTrue(empty($files));
	}
}