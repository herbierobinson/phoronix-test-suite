<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2010, Phoronix Media
	Copyright (C) 2008 - 2010, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class pts_user_io
{
	public static function read_user_input()
	{
		return trim(fgets(STDIN));
	}
	public static function prompt_user_input($question, $allow_null = false)
	{
		do
		{
			echo PHP_EOL . $question . ': ';
			$answer = pts_user_io::read_user_input();
		}
		while(!$allow_null && empty($answer));

		return $answer;
	}
	public static function display_interrupt_message($message)
	{
		if(!empty($message))
		{
			echo $message . PHP_EOL;

			if((pts_c::$test_flags ^ pts_c::batch_mode) && (pts_c::$test_flags ^ pts_c::auto_mode))
			{
				echo PHP_EOL . 'Hit Any Key To Continue...' . PHP_EOL;
				pts_user_io::read_user_input();
			}
		}
	}
	public static function display_text_list($list_items, $line_start = '- ')
	{
		$list = null;

		foreach($list_items as &$item)
		{
			$list .= $line_start . $item . PHP_EOL;
		}

		return $list;
	}
	public static function prompt_bool_input($question, $default = true, $question_id = 'UNKNOWN')
	{
		// Prompt user for yes/no question
		if((pts_c::$test_flags & pts_c::batch_mode))
		{
			switch($question_id)
			{
				case 'SAVE_RESULTS':
					$auto_answer = pts_config::read_user_config(P_OPTION_BATCH_SAVERESULTS, 'TRUE');
					break;
				case 'OPEN_BROWSER':
					$auto_answer = pts_config::read_user_config(P_OPTION_BATCH_LAUNCHBROWSER, 'FALSE');
					break;
				case 'UPLOAD_RESULTS':
					$auto_answer = pts_config::read_user_config(P_OPTION_BATCH_UPLOADRESULTS, 'TRUE');
					break;
				default:
					$auto_answer = 'true';
					break;
			}

			$answer = pts_strings::string_bool($auto_answer);
		}
		else
		{
			$question .= ' (' . ($default == true ? 'Y/n' : 'y/N') . '): ';

			do
			{
				pts_client::$display->generic_prompt($question);
				$input = strtolower(pts_user_io::read_user_input());
			}
			while($input != 'y' && $input != 'n' && $input != '');

			switch($input)
			{
				case 'y':
					$answer = true;
					break;
				case 'n':
					$answer = false;
					break;
				default:
					$answer = $default;
					break;
			}
		}

		return $answer;
	}
	public static function prompt_text_menu($user_string, $options_r, $allow_multi_select = false, $return_index = false)
	{
		$option_count = count($options_r);

		if($option_count == 1)
		{
			return $return_index ? array_pop(array_keys($options_r)) : array_pop($options_r);
		}

		do
		{
			echo PHP_EOL;
			$key_index = array();
			foreach(array_keys($options_r) as $i => $key)
			{
				$key_index[$i] = $key;
				echo ($i + 1) . ': ' . str_repeat(' ', strlen($option_count) - strlen(($i + 1))) . $options_r[$key] . PHP_EOL;
			}
			echo PHP_EOL . $user_string . ': ';
			$select_choice = pts_user_io::read_user_input();

			// Validate possible multi-select
			$multi_choice = pts_strings::comma_explode($select_choice);
			$multi_select_pass = false;

			if($allow_multi_select && count($multi_choice) > 1)
			{
				$multi_select = array();
				foreach($multi_choice as $choice)
				{
					if(in_array($choice, $options_r) || isset($key_index[($choice - 1)]) && (($return_index && $choice = $key_index[($choice - 1)]) || $choice = $options_r[$key_index[($choice - 1)]]) != null)
					{
						array_push($multi_select, $choice);
					}
				}

				if(count($multi_select) > 0)
				{
					$multi_select_pass = true;
					$select_choice = implode(',', $multi_select);
				
				}
			}
		}
		while(!$multi_select_pass && !(in_array($select_choice, $options_r) || isset($options_r[$key_index[($select_choice - 1)]]) && (($return_index && $select_choice = $key_index[($select_choice - 1)]) || $select_choice = $options_r[$key_index[($select_choice - 1)]]) != null));

		return $select_choice;
	}
}

?>
