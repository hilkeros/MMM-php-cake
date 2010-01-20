<?php
/** SVN FILE: $Id$ */
/**
 * CakePHP Helper Class to ease the creation of charts using Open Flash Chart (http://teethgrinder.co.uk/open-flash-chart/)
 *
 * @author JoaquÃ­n WindmÃ¼ller <http://aikon.com.ve/>
 * @copyright JoaquÃ­n WindmÃ¼ller, 14 May, 2008
 * Distributed under the GPL Licence - Keep this message
 **/

vendor('open-flash-chart');
//App::import('Vendor', 'open-flash-chart');

class FlashChartHelper extends Helper {

	/**
	 * Helpers used.
	 **/
	var $helpers = array('Html');

	/**
	 * Graph object provided by Open Flash Chart
	 **/
	var $graph = null;
	
	/**
	 * Allows detection of glass effects.
	 **/
	var $hasGlass = false;
	
	/**
	 * Contains the types of graphs used
	 *
	 * @var array
	 */
	var $types = array();
	
	/**
	 * True if the helper should calculate automatically the range of the axis
	 *
	 * @var boolean
	 */
	var $autoRange = false;
	
	var $data = array();
	/**
	 * Begin the chart creation.
	 * 
	 * @param int width of the chart
	 * @param int height of the chart 
	 * @param string $x_legend legend to place on the x axis.
	 * @param string $y_legend legend to place on the y axis.
	 * @param boolean $autoRange wether ranges should be calculated automatically.
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 **/
	function begin($width=null, $height=null, $x_legend = 'X', $y_legend = 'Y', $autoRange = true) {
		$this->graph = new graph();
		$this->types = array();
		$this->data = array('x'=>array(), 'y'=>array());
		if ($autoRange) {
			$this->graph->set_x_min(-0.001);
			$this->graph->set_x_max(0);
			$this->graph->set_y_min(-0.001);
			$this->graph->set_y_max(-0.001);
			$this->autoRange = true;
		}

		$this->graph->js_path	= $this->Html->url('/') . 'js/';
		$this->graph->swf_path	= $this->Html->url('/');
		$this->graph->set_output_type('js');
		$this->graph->set_bg_colour('#ffffff');
		$this->hasGlass = false;

		if ($width!=null) {
			$this->graph->set_width($width);
		}
		if ($height!=null) {
			$this->graph->set_height($height);
		}
		$this->setToolTip();
		$this->configureGrid(
			array(
				'x_axis' => array(
					'legend' => $x_legend
				),
				'y_axis' => array(
					'legend' => $y_legend
				)
			)
		);
	}

	/**
	 * Sets the Y-axis start value on the chart.
	 * author babar ali
	 **/
	
	function set_y_min($val)
		{	
			$this->graph->set_y_min($val);
		}

	/**
	 * Sets the Y-axis end value on the chart.
	 * author babar ali
	 **/
	
	function set_y_max($val)
		{   //echo $val; exit;
			$this->graph->set_y_max($val);
		}

	/**
	 * Sets the tooltip message on the chart.
	 * 
	 * @param String tooltip message
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 **/
	function setToolTip($tooltip = '') {
		$this->graph->set_tool_tip($tooltip);
	}
	
	/**
	 * Sets the title of the chart as well as its styling
	 *
	 * @param String title of the chart 
	 * @param array styling options
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 **/
	function title($title='', $options = array('font-size' => '14px', 'color'=>'#660000')) {
		$options = $this->__prepareOptions($options);
		$this->graph->title($title, $options);
	}
	
	/**
	 * Sets the data (one or more set) to show in the chart.
	 * 
	 * @param Array of data sets. Each one describes its own styling.
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 **/
	function setData($data_sets) {

		foreach ($data_sets as $legend => $info) {

			$width		= $this->__prepareValue(3, 'width', $info);
			$color		= $this->__prepareValue('#000000', 'color', $info);
			$font_size	= $this->__prepareValue(10, 'font_size', $info);
			$circle		= $this->__prepareValue(-1, 'circle', $info);
			$alpha = $this->__prepareValue(60, 'alpha', $info);

			$data = $info['data'];
			if (isset($info['format'])) {
				$data = Set::extract($data, $info['format']);
			}
			$graph_style = $this->__prepareValue('', 'graph_style', $info);
			if ($graph_style!='scatter') {
				$this->data['x'] = Set::merge(array_keys($data), $this->data['x']);
				$this->data['y'] = Set::merge(array_values($data), $this->data['y']);
				$this->graph->set_data($data);
			}
			$this->types[] = $graph_style;
			if(isset($info['links'])) {
				$links = $info['links'];
				$this->graph->set_links($links);
			}
			
			switch ($graph_style) {
				case 'scatter':
					$this->__scatter(array($legend => $info));
					break;
				case 'bar_sketch':
					$offset = $this->__prepareValue(4, 'offset', $info);
					$alpha = $this->__prepareValue(60, 'alpha', $info);
					list($r,$g,$b) = $this->__html2rgb($color);
					$outline_color = $this->__prepareValue($this->__rgb2html($r-40,$g-40,$b-40), 'outline_color', $info);
					$this->graph->bar_sketch($alpha, $offset, $color, $outline_color, $legend, $font_size);
					break;
				case 'bar_glass':
					$this->hasGlass = true;
				case 'bar_filled':
					$alpha = $this->__prepareValue(60, 'alpha', $info);
					list($r,$g,$b) = $this->__html2rgb($color);
					$outline_color = $this->__prepareValue($this->__rgb2html($r-40,$g-40,$b-40), 'outline_color', $info);
					$this->graph->{"$graph_style"}($alpha, $color, $outline_color, $legend, $font_size);
					break;
				case 'bar_3D':
					$axis_3d_height = $this->__prepareValue(10, 'axis_3d_height', $info);
					$this->graph->set_x_axis_3d($axis_3d_height);
					$default_alpha = 90;
				case 'bar_fade':
					if (!isset($default_alpha)) {
						$default_alpha = 70;
					}
				case 'bar':
					if (!isset($default_alpha)) {
						$default_alpha = 60;
					}
					$alpha = $this->__prepareValue($default_alpha, 'alpha', $info);
					$this->graph->{"$graph_style"}($alpha, $color, $legend, $font_size);
					break;
				case 'line_hollow':
					$this->graph->line_hollow($width, $width+2, $color, $legend, $font_size);
					break;
				case 'line_dot':
					$this->graph->line_dot($width, $width+2, $color, $legend, $font_size);
					break;
				default:
					$this->graph->line($width, $color, $legend, $font_size, $circle);
			}
		}
	}
	
	/**
	 * Helper function to manage scattered point graphs.
	 *
	 * @param data set
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 **/
	function __scatter($info) {
		$legend 	= key($info);
		$info		= current($info);
		
		$width		= $this->__prepareValue(1, 'width', $info);
		$color		= $this->__prepareValue('#000000', 'color', $info);
		$font_size	= $this->__prepareValue(10, 'font_size', $info);
		
		if ($this->autoRange) {
			$x_values = Set::extract('/x', $info['data']);
			$y_values = Set::extract('/y', $info['data']);
			$this->data['x'] = Set::merge($x_values, $this->data['x']);
			$this->data['y'] = Set::merge($y_values, $this->data['y']);
		}

		$alpha = $this->__prepareValue(60, 'alpha', $info);
		foreach ($info['data'] as $i => $point) {
			$dot_width = $this->__prepareValue(4, 'dot_width', $point);
			$data[$i] = new point($point['x'], $point['y'], $dot_width);
		}
		$this->graph->scatter($data, $width, $color, $legend, $font_size);
	}
	
	/**
	 * Manages pie charts.
	 *
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 **/
	function pie($data, $alpha=60, $line_color = '#505050', $style = array('font-size' => '12px', 'color' => '#333333')) {
		$style = $this->__prepareOptions($style);
		$this->graph->pie($alpha, $line_color, $style);
		$r = rand(100,200);
		$g = rand(0,100);
		$b = rand(200,255);
		$which = $r%3;
		$labels = array();
		$vals = array();
		foreach ($data as $label => $value) {
			$labels[] = $label;
			$vals[] = $value;
			if (isset($value['color'])) continue;
			$data[$label]['color'] = $this->__rgb2html($r, $g, $b);
			switch ($which) {
				case 0:
					$r = ($r+100)%255;
					break;
				case 1:
					$g = ($g+100)%255;
					break;
				case 2:
					$b = ($b+100)%255;
					break;
			}
			$which = ($which+1)%3;
		}
		
	//	$values = array_values(Set::extract($data, '{}.value'));
		$this->graph->pie_values($vals, $labels);
		 //$colors = array_values(Set::extract($data, '{}.color'));
		for($i=0; $i< count($vals); $i++)
		   { 
		       $colors[$i]=sprintf("#%u%u%u%u%u%u",
				                      dechex(mt_rand(0,15)),
				                      dechex(mt_rand(0,15)),
				                      dechex(mt_rand(0,15)),
				                      dechex(mt_rand(0,15)),
				                      dechex(mt_rand(0,15)),
				                      dechex(mt_rand(0,15))
				                     );
		   }		
//$colors= array('#d01f3c','#356aa0','#C79810');
		$this->graph->pie_slice_colours($colors);
		$this->setToolTip('#x_label#<br>#val#');
		$this->data = null;
	}
	
	/**
	 * Sets the minimum and maximum range of an axis. This should be called on a graph with autoRange set to false
	 *
	 * @param string $axis 'x' or 'y'
	 * @param float $min minimum value for axis
	 * @param float $max maximum value for axis 
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function setRange($axis, $min=null, $max=null) {
		if ($axis=='x' || $axis=='y') {
			if ($min!=null) {
				$this->graph->{"set_{$axis}_min"}($min);
			}
			if ($max!=null) {
				$this->graph->{"set_{$axis}_max"}($max);
			}
		}
	}
	
	/**
	 * Sets the step of an axis
	 *
	 * @param string $axis 'x' or 'y'
	 * @param int $step size of the step
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function setStep($axis, $step) {
		if ($axis=='x' || $axis=='y') {
			$this->graph->{"{$axis}_label_step"}($step);
		}
	}
	
	/**
	 * Sets the styes for the grid
	 *
	 * @param array $grid_options array with two keys 'x_axis' and 'y_axis' each pointing to an array of styles and configurations.
	 * @param string $bg_color background color in hex notation
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function configureGrid($grid_options, $bg_color = '#ffffff') {
		$this->__setAxisStyle('x', $grid_options);
		$this->__setAxisStyle('y', $grid_options);
		if (isset($grid_options['labels']) && is_array($grid_options['labels'])) {
			$this->labels($grid_options['labels']);
		}
		$this->setBackgroundColor($bg_color);
	}

	/**
	 * Sets custom style to the x axis
	 *
	 * @param array $id , style , format array of string
	 * @return void
	 */
	function set_x_style($id,$style,$format) {
		$this->graph->set_x_label_style($id, $style ,$format );
	}
	/**
	 * Sets custom lables to the x axis
	 *
	 * @param array $labels array of string
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function labels($labels) {
		$this->graph->set_x_labels($labels);
	}
	
	/**
	 * Sets the background color for the chart
	 *
	 * @param string $color background color in hex notation
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function setBackgroundColor($color) {
		$this->graph->set_bg_colour($color);
	}
	
	/**
	 * Sets the axis styles and legend 
	 *
	 * @param string $axis 'x' or 'y'
	 * @param array $grid_options array with two keys 'x_axis' and 'y_axis' each pointing to an array of styles and configurations
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function __setAxisStyle($axis, $styles) {
		if ($axis=='x' || $axis=='y') {
			if (isset($styles[$axis.'_axis'])) {
				$style_info = $styles[$axis.'_axis'];
				$size			= $this->__prepareValue(9, 'size', $style_info);
				$color			= $this->__prepareValue('#666633', 'color', $style_info);
				$orientation	= $this->__prepareValue(0, 'orientation', $style_info); // Only works for x
				$step			= $this->__prepareValue(-1, 'step', $style_info);
				$grid_marks		= $this->__prepareValue('#000000', 'grid_marks', $style_info);
				$this->graph->{"set_{$axis}_label_style"}($size, $color, $orientation, $step, $grid_marks);

				$grid			= $this->__prepareValue('#dddddd', 'grid', $style_info);
				$this->graph->{"{$axis}_axis_colour"}($color, $grid);

				$legend			= $this->__prepareValue('', 'legend', $style_info);
				$legend_size	= $this->__prepareValue(12,  'legend_size', $style_info);
				$legend_color	= $this->__prepareValue($color, 'legend_color',	$style_info);
				$this->setLegend($axis, $legend, $legend_size, $legend_color);
			}
		}
	}
	
	/**
	 * Set the legend for an axis
	 *
	 * @param string $axis 'x' or 'y'
	 * @param string $legend Legend for the axis
	 * @param int $legend_size font size for the legend
	 * @param string $legend_color color for the legend in hex notation
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function setLegend($axis, $legend = null, $legend_size=12, $legend_color='#666666') {
		if (empty($legend)) {
			$legend = Inflector::humanize($axis);
		}
		$this->graph->{"set_{$axis}_legend"}($legend, $legend_size, $legend_color);
	}
	
	/**
	 * Helper function that converts an associative array into css style notation.
	 *
	 * @param array $options associative array with options
	 * @return string options in css notation
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function __prepareOptions($options) {
		$options_str = '';
		foreach($options as $key => $value) {
			$options_str .= $key . ':' . $value . ';';
		}
		return '{' . $options_str . '}';
	}
	
	/**
	 * Returns the value of an array or the default if not set 
	 *
	 * @param string $default default value if not set
	 * @param string $key key to search inside the array
	 * @param array $arr array in which to search
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function __prepareValue($default, $key, $arr) {
		$value = $default;
		if (isset($arr[$key])) {
			$value = $arr[$key];
		}
		return $value;
	}

	/**
	 * Renders the graph
	 *
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function render() {
		$this->__calculateRanges();
		if ($this->hasGlass) {
			$this->graph->x_axis_3d = '';
		}
		return $this->graph->render();
	}
	
	/**
	 * Calculates optimal ranges for axis
	 *
	 * @return void
	 * @author JoaquÃ­n WindmÃ¼ller 
	 * @access private
	 */
	function __calculateRanges(){
		if (!empty($this->data) && $this->autoRange) {

			$step_x = array_sum($this->data['x'])/count($this->data['x']);
			$this->setRange('x', min($this->data['x']), max($this->data['x']));
			
			sort($this->data['y'], SORT_NUMERIC);
			$max = $this->data['y'][count($this->data['y'])-1];
			$increment = pow(10, strlen("$max") - 1) * .5;

			$min_y = $max_y = 0;
			if (min($this->data['y'])<0) {
				while ($min_y > min($this->data['y'])) {
					$min_y -= $increment;
				}
				$min_y -= $increment;
			}
			while ($max_y < max($this->data['y'])) {
				$max_y += $increment;
			}
			$max_y += $increment;
			
			$this->setRange('y', $min_y, $max_y);
		}
	}
	
	/**
	 * Determines wether this chart has an specific type of graphic
	 *
	 * @param string $type type of graph
	 * @return boolean
	 * @author JoaquÃ­n WindmÃ¼ller
	 */
	function has($type='', $unique = false) {
		$types = $this->types;
		if ($unique) {
			$types = array_unique($this->types);
		}
		return in_array($type, $types);
	}
	
	/**
	 * @see http://www.anyexample.com/programming/php/php_convert_rgb_from_to_html_hex_color.xml
	 */
	function __html2rgb($color) {
		if ($color[0] == '#')
			$color = substr($color, 1);
		if (strlen($color) == 6)
			list($r, $g, $b) = array(
				$color[0].$color[1],
				$color[2].$color[3],
				$color[4].$color[5]
			);
		elseif (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
			return false;
		
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		
		return array($r, $g, $b);
	}
	
	/**
	 * @see http://www.anyexample.com/programming/php/php_convert_rgb_from_to_html_hex_color.xml
	 */
	function __rgb2html($r, $g=-1, $b=-1){
		if (is_array($r) && sizeof($r) == 3)
			list($r, $g, $b) = $r;
		
		$r = intval($r);
		$g = intval($g);
		$b = intval($b);

		$r = dechex($r<0?0:($r>255?255:$r));
		$g = dechex($g<0?0:($g>255?255:$g));
		$b = dechex($b<0?0:($b>255?255:$b));
		
		$color = (strlen($r) < 2?'0':'').$r;
		$color .= (strlen($g) < 2?'0':'').$g;
		$color .= (strlen($b) < 2?'0':'').$b;
		return '#'.$color;
	}
}
?>
