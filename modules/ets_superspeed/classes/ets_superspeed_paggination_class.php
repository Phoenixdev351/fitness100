<?php
/**
 * 2007-2020 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2020 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
	exit;
class Ets_superspeed_paggination_class {
	public $total = 0;
	public $page = 1;
	public $limit = 20;
	public $num_links = 10;
	public $url = '';
	public $text = 'Showing {start} to {end} of {total} ({pages} Pages)';
	public $text_first = '';
	public $text_last = '';
	public $text_next = '';
	public $text_prev = '';
	public $style_links = 'links';
	public $style_results = 'results';
    public function __construct()
    {
          $this->text_first = '<' . 's' . 'p' . 'a' . 'n' . '>|&lt;<' . '/' . 's' . 'p' . 'a' . 'n' . '>';
          $this->text_last ='<' . 's' . 'p' . 'a' . 'n' . '>&gt;|<' . '/' . 's' . 'p' . 'a' . 'n' . '>'; 
          $this->text_next = '<' . 's' . 'p' . 'a' . 'n' . '>' . '&gt;' . '<' . '/' . 's' . 'p' . 'a' . 'n' . '>'; 
          $this->text_prev = '<' . 's' . 'p' . 'a' . 'n' . '>' . '&lt;<' . '/' . 's' . 'p' . 'a' . 'n' . '>'; 
    }
	public function render() {
	    
		$total = $this->total;
		if($total<=1)
            return false;
		if ($this->page < 1) {
			$page = 1;
		} else {
			$page = $this->page;
		}
		
		if (!(int)$this->limit) {
			$limit = 10;
		} else {
			$limit = $this->limit;
		}
		
		$num_links = $this->num_links;
		$num_pages = ceil($total / $limit);
		
		$output = '';
		
		if ($page > 1) {
			$output .= ' <' . 'a'. ' class = "frist" href="' . $this->replacePage(1) . '">' . $this->text_first . '<' . '/' .'a'.'> <' . 'a' . ' class = "prev" href="' . $this->replacePage($page-1) . '">' . $this->text_prev . '<' . '/' . 'a' . '> ';
    	}

		if ($num_pages > 1) {
			if ($num_pages <= $num_links) {
				$start = 1;
				$end = $num_pages;
			} else {
				$start = $page - floor($num_links / 2);
				$end = $page + floor($num_links / 2);
			
				if ($start < 1) {
					$end += abs($start) + 1;
					$start = 1;
				}
						
				if ($end > $num_pages) {
					$start -= ($end - $num_pages);
					$end = $num_pages;
				}
			}

			if ($start > 1) {
				$output .= ' .... ';
			}

			for ($i = $start; $i <= $end; $i++) {
				if ($page == $i) {
					$output .= ' <' . 'b' . '>' . $i . '<' . '/' . 'b' . '> ';
				} else {
					$output .= ' <' . 'a' . ' href="' . $this->replacePage($i) . '">' . $i . '<' . '/' . 'a' . '> ';
				}	
			}
							
			if ($end < $num_pages) {
				$output .= ' .... ';
			}
		}
		
   		if ($page < $num_pages) {
			$output .= ' <' . 'a' . ' class = "next" href="' . $this->replacePage($page+1) . '">' . $this->text_next . '<' . '/' . 'a' . '> <' . 'a' . ' class = "last" href="' . $this->replacePage($num_pages) . '">' . $this->text_last . '<' . '/' . 'a' . '> ';
		}
		
		$find = array(
			'{start}',
			'{end}',
			'{total}',
			'{pages}'
		);
		
		$replace = array(
			($total) ? (($page - 1) * $limit) + 1 : 0,
			((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit),
			$total, 
			$num_pages
		);
		if($num_pages==1)
            $this->text= 'Showing {start} to {end} of {total} ({pages} Page)';
		return ($output ? '<' . 'd' . 'i' . 'v' . ' class="links">' . $output . '<' . '/' . 'd' . 'i' . 'v' . '>' . '' : '') . '<' . 'd' . 'i' . 'v' . ' class="' . $this->style_results . '">' . str_replace($find, $replace, $this->text) . '<' . '/' . 'd' . 'i' . 'v' . '>';
	}
    public function replacePage($page)
    {
        return str_replace('_page_', $page, $this->url);            
    }
}
?>