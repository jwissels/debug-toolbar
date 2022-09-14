<?php

namespace alsvanzelf\debugtoolbar\parts;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\models\Metric;
use alsvanzelf\debugtoolbar\models\PartAbstract;
use alsvanzelf\debugtoolbar\models\PartInterface;

class TwigPart extends PartAbstract implements PartInterface {
	public static $trackedProfilers = [];
	
	public function name() {
		return 'Twig';
	}
	
	public function title() {
		return 'Twig';
	}
	
	public static function trackProfiler(/* disabled to support twig v1 - v3 \Twig\Profiler\Profile*/ $profiler) {
		self::$trackedProfilers[] = $profiler;
	}
	
	public static function track() {
		$profilers = self::$trackedProfilers;
		if (empty($profilers)) {
			return null;
		}
		
		// support twig v1 - v3
		if (class_exists('\Twig_Profiler_Dumper_Html')) {
			$dumper = new \Twig_Profiler_Dumper_Html();
		}
		else {
			$dumper = new \Twig\Profiler\Dumper\HtmlDumper();
		}
		
		$renderCount   = 0;
		$duration      = 0;
		$memoryCurrent = 0;
		$memoryPeak    = 0;
		$htmlDump      = '';
		foreach ($profilers as $profiler) {
			if (empty($profiler->getProfiles())) {
				continue;
			}
			
			$renderCount   += self::countTemplateRenders($profiler->getProfiles());
			$duration      += $profiler->getDuration();
			$memoryCurrent += $profiler->getMemoryUsage();
			$memoryPeak    += max($memoryPeak, $profiler->getPeakMemoryUsage());
			$htmlDump      .= $dumper->dump($profiler);
		}
		
		$data = [
			'render_count'   => $renderCount,
			'duration'       => $duration,
			'memory_current' => self::memoryBytesToString($memoryCurrent),
			'memory_peak'    => self::memoryBytesToString($memoryPeak),
			'html_dump'      => $htmlDump,
		];
		
		return $data;
	}
	
	public function metrics() {
		$rendersValue  = null;
		$rendersDetail = null;
		if ($this->logData->render_count > 0) {
			$rendersValue  = $this->logData->render_count.' templates';
			$rendersDetail = new Detail('profiler');
		}
		
		$durationValue = null;
		if ($this->logData->duration) {
			$durationValue = round($this->logData->duration, 3).' s';
		}
		
		$metrics = [
			new Metric('Renders',  $rendersValue, $featured=true, $alert=null, $rendersDetail),
			new Metric('Duration', $durationValue),
			self::metricMemoryPeak(),
		];
		
		return $metrics;
	}
	
	public function detail(Detail $detail) {
		switch ($detail->key) {
			case 'profiler': return $this->detailProfiler();
			case 'memory':   return $this->detailMemory();
			default:         throw new Exception('unknown detail key');
		}
	}
	
	private function detailProfiler() {
		$data = [
			'htmlDump' => $this->logData->html_dump,
		];
		
		return $data;
	}
	
	private function detailMemory() {
		$data = [
			'memoryCurrentMetric' => $this->metricMemoryCurrent(),
			'memoryPeakMetric'    => $this->metricMemoryPeak($forDetail=true),
		];
		
		return $data;
	}
	
	private function metricMemoryCurrent() {
		$alert  = (self::memoryStringToBytes($this->logData->memory_current) > self::memoryStringToBytes(ini_get('memory_limit')) / 4) ? '> 25% of set memory limit ('.ini_get('memory_limit').')' : null;
		$metric = new Metric('During Twig renders', $this->logData->memory_current, $featured=false, $alert);
		
		return $metric;
	}
	
	private function metricMemoryPeak($forDetail=false) {
		$title    = ($forDetail) ? 'Peak' : 'Memory';
		$value    = null;
		$featured = false;
		$alert    = null;
		$detail   = null;
		
		if ($this->logData->memory_peak) {
			$value    = ($forDetail) ? $this->logData->memory_peak : $this->logData->memory_peak.' (peak)';
			$alert    = (self::memoryStringToBytes($this->logData->memory_peak) > self::memoryStringToBytes(ini_get('memory_limit')) / 4) ? '> 25% of set memory limit ('.ini_get('memory_limit').')' : null;
			$detail   = new Detail($key='memory', $mode=Detail::MODE_INLINE);
		}
		
		$metric = new Metric($title, $value, $featured, $alert, $detail);
		
		return $metric;
	}
	
	private static function memoryStringToBytes($byteString) {
		preg_match('{(?<bytes>[0-9.]+) ?(?<unit>[A-Z])}', $byteString, $match);
		$bytes = (float) $match['bytes'];
		
		if (empty($match['unit']) || $match['unit'] == 'B') {
			return $bytes;
		}
		
		$units    = ['K'=>1, 'M'=>2, 'G'=>3, 'T'=>4];
		$exponent = $units[$match['unit']];
		$bytes    = $bytes * pow(1024, $exponent);
		
		return $bytes;
	}
	
	private static function memoryBytesToString($bytes) {
		if ($bytes > 1024 * 1024) {
			return round($bytes / 1024 / 1024, 2).' MB';
		}
		elseif ($bytes > 1024) {
			return round($bytes / 1024, 2).' KB';
		}
		
		return $bytes . ' B';
	}
	
	private static function countTemplateRenders($profiles, $count=0) {
		// support twig v1 - v3
		if (class_exists('\Twig_Profiler_Profile')) {
			$templateType = \Twig_Profiler_Profile::TEMPLATE;
		}
		else {
			$templateType = \Twig\Profiler\Profile::TEMPLATE;
		}
		
		foreach ($profiles as $profile) {
			if ($profile->getType() == $templateType) {
				$count++;
			}
			
			$subProfiles = $profile->getProfiles();
			if (count($subProfiles) > 0) {
				$count = self::countTemplateRenders($subProfiles, $count);
			}
		}
		
		return $count;
	}
}
