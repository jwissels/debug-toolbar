<?php

namespace alsvanzelf\debugtoolbar\parts;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\models\Metric;
use alsvanzelf\debugtoolbar\models\PartAbstract;
use alsvanzelf\debugtoolbar\models\PartInterface;

class TwigPart extends PartAbstract implements PartInterface {
	public static $trackedProfiler = null;
	
	public function name() {
		return 'Twig';
	}
	
	public function title() {
		return 'Twig';
	}
	
	public static function trackProfiler(\Twig_Profiler_Profile $profiler) {
		self::$trackedProfiler = $profiler;
	}
	
	public static function track() {
		$profiler = self::$trackedProfiler;
		if (empty($profiler) || empty($profiler->getProfiles())) {
			return null;
		}
		
		$counts = self::countProfileTypes($profiler->getProfiles());
		$dumper = new \Twig_Profiler_Dumper_Html();
		
		$data = [
			'count_templates' => $counts['template'],
			'count_blocks'    => $counts['block'],
			'count_macros'    => $counts['macro'],
			'duration'        => $profiler->getDuration(),
			'memory_current'  => self::memoryBytesToString($profiler->getMemoryUsage()),
			'memory_peak'     => self::memoryBytesToString($profiler->getPeakMemoryUsage()),
			'html_dump'       => $dumper->dump($profiler),
		];
		
		return $data;
	}
	
	public function metrics() {
		$rendersValue  = null;
		$rendersDetail = null;
		$allCounts     = ($this->logData->count_templates + $this->logData->count_blocks + $this->logData->count_macros);
		if ($allCounts > 0) {
			$rendersValue  = $this->logData->count_templates.' templates, '.$this->logData->count_blocks.' blocks, '.$this->logData->count_macros.' macros';
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
		
		if (empty($match['unit'])) {
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
	
	private static function countProfileTypes($profiles, $counts=[]) {
		if (empty($counts)) {
			$counts['template'] = 0;
			$counts['block']    = 0;
			$counts['macro']    = 0;
		}
		
		foreach ($profiles as $profile) {
			$counts[$profile->getType()]++;
			
			$subProfiles = $profile->getProfiles();
			if (count($subProfiles) > 0) {
				$counts = self::countProfileTypes($subProfiles, $counts);
			}
		}
		
		return $counts;
	}
}
