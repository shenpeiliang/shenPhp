<?php
namespace Core;
/**
 * 模板引擎处理
 * Class View
 */
class View
{
	/**
	 * 模板输出变量
	 * @var array
	 */
	protected $var = [];

	/**
	 * ob缓存层级
	 * @var int
	 */
	private $current_ob_level = 0;

	/**
	 * 模板内容
	 * @var string
	 */
	private $content = '';

	function __construct()
	{
		$this->current_ob_level = ob_get_level();
	}

	/**
	 * 模板变量赋值
	 * @param $name
	 * @param $value
	 */
	public function assign($name, $value)
	{
		if (is_array($name))
			$this->var = array_merge($this->var, $name);
		else
			$this->var[$name] = $value;

		return $this;
	}

	/**
	 * 模板内容输出
	 * @param string $template_file
	 * @param bool $is_return
	 * @return string
	 */
	public function display(string $template_file = '', $is_return = FALSE)
	{
		//模板绝对路径
		$absolute_path = $this->get_template_path($template_file);
		if(!is_file($absolute_path))
			throw \Core\Exceptions\FileException::for_not_found();

		//模板阵列变量分解成为独立变量
		extract($this->var, EXTR_OVERWRITE);

		//页面缓存
		ob_start();

		//关闭绝对刷送
		ob_implicit_flush(0);

		include $absolute_path;

		//嵌套输出
		if(ob_get_level() > $this->current_ob_level)
			ob_end_flush();
		else
			$this->content .= ob_get_contents();

		//获取并清空缓存
		$content = ob_get_clean();

		//直接返回结果，不输出
		if($is_return)
			return $content;

		//网页字符编码
		header('Content-Type:text/html; charset='. config('common.default_charset'));
		//页面缓存控制
		header('Cache-control: '. config('common.http_cache_control'));
		echo $content;
	}

	/**
	 * 获取模板文件路径
	 * @param string $template_file
	 * @return string
	 */
	private function get_template_path(string $template_file = ''): string
	{
		if($template_file)
			return APP_PATH . 'View/' . $template_file . config('common.template_suffix');

		//基于目录+控制名+方法名
		$uri = new \Core\URI();
		$uri_segments = $uri->get_uri_segments();

		return APP_PATH . 'View/' . implode(config('common.template_delimiter'), $uri_segments) . config('common.template_suffix');
	}
}