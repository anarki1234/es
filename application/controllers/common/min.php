<?php defined('SYSPATH') OR exit('POWERED BY Enozoomstudio');
/**
* min�����з����޷����ݶ������
* @author joe e@enozoom.com
* 
* �� index.php?c=min&m=index&d=common&q=1,2,3.css
* function index($a,$b,$c){ }
* 
* ��������»���� $a=1;$b=2;$c=3
* ��min����������  $a='1,2,3';$b=null;$c=null
* -------------------------------------------
* ����
* 2015��2��13��
* ����CSS�ϲ��ļ�����ȥ�ո�
* ---------------------
* 2015��5��23��10:51:10
* �����ӶԲ�ͬ�ļ��е�֧��
* �����ļ�����gzipѹ��
* ---------------------
* 2015��6��25��13:38:10
* �����Ӷ�less֧��
* �����뻺��
* ---------------------
* 2015��8��7��14:19:57
* ���޸���ȡ����ʱ��Ӧͷ��Ϣ����
* ������ȫ������$compress���㿪��gzipѹ��
* ---------------------
*  2015��9��13��15:30:46
* ��ʹ���ⲿ��configs/config.eno�����theme_path�����壬css,js,less�Ĵ��·��
* 
*/
class min extends ES_controller{
	private $cache = FALSE;
	private $compress = FALSE;
	private $cache_dir;
	private $cache_suffix = '.clej';
	private $def_dir;
	public function __construct(){
		parent::__construct();
        global $configs;
		$this->cache_dir = APPPATH.'cache/cssjsless/';
        $this->def_dir = $configs->config->theme_path;
	}
/**
 * ���
 * @param string $files �ļ����ַ���+�ļ����ͺ�׺����base,dom.css������ļ���","�ָ�����պ�׺�����������
 */
	public function index($files=''){
        
		$output = $this->cache($files);
		$suffix = substr($files,strrpos($files,'.')+1);// ��ȡ��׺
		empty($output) || $this->compress($output,$suffix);
	}
	
/**
 * �Ժϲ��ļ�����css���
 * @param string $str
 * @return string
 */		private function _css($str=''){
		$str = preg_replace(array('/{\s*([^}]*)\s*}/','/\s*:\s*/','~\/\*[^\*\/]*\*\/~s'),array('{$1}',':',''),$str);
		$str = preg_replace(array('/'.PHP_EOL.'/','/\n*/'),'',$str);

		return $str;
	}
	
/**
 * �Ժϲ��ļ�����js���
 * @param string $str
 * @return string
 */	
	private function _js($str=''){
		return $str;
	}

/**
 * �ϲ�����ļ�����css��������ǰ����less����Ϊcss
 * @param string $str
 * @return mixed
 */	
	private function _less($str=''){
		$this->load->library('Less/Less_Parser','less');
		$this->less->parse($str);
		return $this->_css($this->less->getCss());
	}

/**
 * ѹ���ַ���
 * @param string $str
 * @return void ֱ�������ҳ��
 */	
	private function compress($str,$suffix='css'){
		switch ($suffix){
			case 'css':case 'less':
				header('Content-type:text/css');
			break;case 'js':
				header('Content-type:application/x-javascript');
			break;
		}
		
		$this->compress && extension_loaded('zlib') && ob_start('ob_gzhandler');
		echo $str;
		$this->compress && extension_loaded('zlib') && ob_end_flush();
	}

/**
 * ��ȡ�򽫺ϲ��ļ������ַ�д�뻺��
 * @param string $files
 * @return string
 */	
	private function cache($files=''){
		$output = '';
		if($this->cache){// �ӻ����ж�ȡ
			$filename = sha1($files).$this->cache_suffix;
			$path = './'.$this->cache_dir.$filename;
			file_exists($path) && $output = file_get_contents($path);
		}		
		$filetype = substr($files,strrpos($files,'.')+1);// ��ȡ��׺
		
		if(empty($output)){// ���ļ��ж�ȡ
			$files = str_replace(".{$filetype}",'',$files);// ȥ��׺
			
			foreach(explode(',',$files) as $f){
				if(strpos($f,'/')===FALSE){
					$path = "./theme/{$this->def_dir}/{$filetype}/{$f}.{$filetype}";
				}else{
					// Ĭ���Զ����Ĭ���ļ���
					$path = "./theme/{$this->def_dir}/{$f}.{$filetype}";
					$path = str_replace("{$this->def_dir}/{$this->def_dir}", $this->def_dir, $path);
				}
			
				// ����Ĭ���ļ���
				file_exists($path)||$path = str_replace("{$this->def_dir}/",'',$path);
				if(file_exists($path)){
					$output .= file_get_contents($path);
				}
			}
			if(!empty($output)){
				$method = "_{$filetype}";
				$output = $this->$method($output);
				$this->cache &&
				file_put_contents($this->cache_dir.$filename, $output);
			}			
		}
		
		return $output;
	}
}