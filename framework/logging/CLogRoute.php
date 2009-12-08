<?php
/**
 * CLogRoute class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLogRoute is the base class for all log route classes.
 *
 * A log route object retrieves log messages from a logger and sends it
 * somewhere, such as files, emails.
 * The messages being retrieved may be filtered first before being sent
 * to the destination. The filters include log level filter and log category filter.
 *
 * To specify level filter, set {@link levels} property,
 * which takes a string of comma-separated desired level names (e.g. 'Error, Debug').
 * To specify category filter, set {@link categories} property,
 * which takes a string of comma-separated desired category names (e.g. 'System.Web, System.IO').
 *
 * Level filter and category filter are combinational, i.e., only messages
 * satisfying both filter conditions will they be returned.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.logging
 * @since 1.0
 */
abstract class CLogRoute extends CComponent
{
	/**
	 * @var boolean whether to enable this log route. Defaults to true.
	 * @since 1.0.7
	 */
	public $enabled=true;
	/**
	 * @var string list of levels separated by comma or space. Defaults to empty, meaning all levels.
	 */
	public $levels='';
	/**
	 * @var string list of categories separated by comma or space. Defaults to empty, meaning all categories.
	 */
	public $categories='';
	/**
	 * @var mixed the additional filter (e.g. {@link CLogFilter}) that can be applied to the log messages.
	 * The value of this property will be passed to {@link Yii::createComponent} to create
	 * a log filter object. As a result, this can be either a string representing the
	 * filter class name or an array representing the filter configuration.
	 * In general, the log filter class should be {@link CLogFilter} or a child class of it.
	 * Defaults to null, meaning no filter will be used.
	 * @since 1.0.6
	 */
	public $filter;
	/**
	 * @var array the logs that are collected so far by this log route.
	 * @since 1.1.0
	 */
	public $logs;


	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
	}

	/**
	 * Formats a log message given different fields.
	 * @param string message content
	 * @param integer message level
	 * @param string message category
	 * @param integer timestamp
	 * @return string formatted message
	 */
	protected function formatLogMessage($message,$level,$category,$time)
	{
		return @date('Y/m/d H:i:s',$time)." [$level] [$category] $message\n";
	}

	/**
	 * Retrieves filtered log messages from logger for further processing.
	 * @param CLogger logger instance
	 * @param boolean whether to process the logs after they are collected from the logger
	 */
	public function collectLogs($logger, $processLogs=false)
	{
		$logs=$logger->getLogs($this->levels,$this->categories);
		if(!empty($logs))
		{
			if($this->filter!==null)
				Yii::createComponent($this->filter)->filter($logs);
			if(empty($this->logs))
				$this->logs=$logs;
			else
				$this->logs=array_merge($this->logs,$logs);
		}
		if($processLogs && !empty($this->logs))
			$this->processLogs($this->logs);
	}

	/**
	 * Processes log messages and sends them to specific destination.
	 * Derived child classes must implement this method.
	 * @param array list of messages.  Each array elements represents one message
	 * with the following structure:
	 * array(
	 *   [0] => message (string)
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true));
	 */
	abstract protected function processLogs($logs);
}
