<?php
/**
 * FriendsRoll Wordpress plugin
 * Copyright (c) 2008 76design/Thornley Fallis Communications
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/


class FriendsrollSecurity {
	var $questions = array(
		2 => array("What color is the sky?", array("blue")),
		56 => array("What is ten minus 6?", array("four", "4")),
		21 => array("How many thumbs does a human have?", array("two", "2")),
		33 => array("How many seasons in a year?", array("four", "4")),
		115 => array("A dozen is how many?", array("twelve", "12")),
	);
	
	function getRandom(&$question) {
		$keys = array_keys($this->questions);
		$rand = mt_rand(0, sizeof($keys)-1);
		$question = $this->questions[$keys[$rand]][0];
		return $keys[$rand];
	}
	
	function validate($answer, $key) {
		return in_array(strtolower($answer), $this->questions[$key][1]);
	}
}