<?php
/*  standardize.php

    Standardize function is required so that the call no can be searched
    properly and easily.

    Here's how the algorithm works:
    a) process function:
        1) make all the alphabets uppercase
        2) remove dates from call no(if any)
        3) strip out white spaces and dots and replace decimal point to '*'
    b) code_extract function
        1) extract each section of codes into an array (ie, NA237.45 C4 M6 has
           total 7 sections, which are NA, 237, 45, C, 4, M, 6)
    c) code_generate function
        1) fills all to default with zeroes and dots (the basic format: 000000000.000000000.000000000.000000000)
        2) put all of the extracted codes in place
    d) fill_z_behind if necessary (ie, inserting std_end entry into db)

    Special notes:
    This script made the following assumptions when removing a date from the
    call no (which is not useful for searching).
    1) The date must be 4 digits long, and may or may not contain a
       character after the 4 digit. (ie, 1990B, 2001a, and 2100 are all valid
       dates).
    2) The first digit must start with a 1 or 2. (ie, 1800 and 2900 are
       valid, but 3200 is not).
    3) There must be a single space before the date.
    4) The date must be located at the end of the call no.
    5) Consist of more than 2 parts. eg, BF 1990 has only 2 parts.

    The program will remove the date from the call no only when the criterias
    above are all met.
*/

/*  Input:  A regular library call number, usually for search purposes.
    Output: A call number with nine digit segments separated by dots. ie,
        AL123.4 C23 M4 will be standardized as
        AL0000123.400000000.C23000000.M40000000
        if adjust is true, then the 0's at the back will be filled with 'z's...
*/
function standardize($callnum, $adjust = false)
{
    $callnum = process($callnum);

    $extracted = code_extract($callnum);
    $generated = code_generate($extracted);

    if ($adjust) {
        fill_z_behind($generated);
    } else {
        replace_hash_to_zero($generated);
    }

    return $generated;
}

function replace_hash_to_zero(&$str)
{
    for ($i = 0; $i < strlen($str); $i++) {
        if ($str[$i] == '#') {
            $str[$i] = '0';
        }
    }
}

// Fill Z behind when inserting a new std_end entry into db (to solve the
// continuity problem, so that it connects to the start of the next range)
function fill_z_behind(&$str)
{
    $len = strlen($str);

    for ($i = $len - 1; $i > 0; $i--) {
        if ($str[$i] == '#') {
            $str[$i] = 'Z';
        } elseif ($str[$i] == '.') {
            continue;
        } else {
            break;
        }
    }

    replace_hash_to_zero($str);
}

// Make call no. all uppercase, remove date, strips white spaces and dots,
// make decimal point to '*'
function process($callnum)
{
    // Make all alphabets uppercase
    $callnum = strtoupper($callnum);

    // Delete the year of the call number if exist (the year must have a space
    // in front, starts with digit 1 or 2, and may or may not have a single
    // character after the year. It must also consist of more than 2 parts.
    if (count_parts($callnum) > 2) {
        $pattern = '/ [12]\d\d\d[A-Za-z]{0,1}$/';
        $replacement = '';
        $callnum = preg_replace($pattern, $replacement, $callnum);
    }

    // Replace the decimal to *, to easily identify decimals
    $pattern = '/([\d])\.([\d])/';
    $replacement = '$1*$2';
    $callnum = preg_replace($pattern, $replacement, $callnum);

    // Delete all occurence of '.' and ' '
    // Note that the decimal is still in place because of the '*'
    $pattern = '/[\. ]/';
    $replacement = '';
    $callnum = preg_replace($pattern, $replacement, $callnum);

    return $callnum;
}

// Count the number of parts in a call number. This is used only in the
// process function to ensure that it doesn't delete part of the call number
// as date, eg: BF 1990.
function count_parts($callnum)
{
    // Make all alphabets uppercase
    $callnum = strtoupper($callnum);

    // Replace the decimal to *, to easily identify decimals
    $pattern = '/([\d])\.([\d])/';
    $replacement = '$1*$2';
    $callnum = preg_replace($pattern, $replacement, $callnum);

    // Delete all occurence of '.' and ' '
    // Note that the decimal is still in place because of the '*'
    $pattern = '/[\. ]/';
    $replacement = '';
    $callnum = preg_replace($pattern, $replacement, $callnum);

    $total_parts = 1;

    for ($i = 0; $i < strlen($callnum) - 1; $i++) {
        if ($callnum[$i] == '*' ||
            (is_alpha($callnum[$i]) && is_digit($callnum[$i+1])) ||
            (is_digit($callnum[$i]) && is_alpha($callnum[$i+1]))) {
            $total_parts++;
        }
    }

    return $total_parts;
}

// Generate a basic format of the standardized call no
function fill_zeroes_dots()
{
    $generated = '';

    for ($i = 0; $i < 39; $i++) {
        if ($i  % 10 == 9) {
            $generated .= '.';
        } else {
            $generated .= '#';
        }
    }

    return $generated;
}

// Generates the standardized code using the extracted code of each section
function code_generate($extracted)
{
    $result = fill_zeroes_dots();

    put_front($extracted[0], $result, 0);

    if (isset($extracted[1])) {
        put_end($extracted[1], $result, 0);
    }

    if (isset($extracted[2])) {
        put_front($extracted[2], $result, 1);
    }

    if (isset($extracted[3]) && isset($extracted[4])) {
        put_front($extracted[3] . $extracted[4], $result, 2);
    } elseif (isset($extracted[3])) {
        put_front($extracted[3], $result, 2);
    }

    if (isset($extracted[5]) && isset($extracted[6])) {
        put_front($extracted[5] . $extracted[6], $result, 3);
    } elseif (isset($extracted[5])) {
        put_front($extracted[5], $result, 3);
    }

    return $result;
}

function put_front($source, &$target, $section_index)
{
    put_str($source, $target, $section_index * 10);
}

function put_end($source, &$target, $section_index)
{
    put_str($source, $target, ($section_index + 1) * 10 - strlen($source) - 1);
}

function put_str($source, &$target, $index)
{
    $len = strlen($source);

    for ($i = 0; $i < $len; $i++) {
        $target[$index + $i] = $source[$i];
    }
}

/*  Extract the code in sections and store in an array

    The callnum provided should contain only either alphabets, digits, and a
    special character (*), without any whitespaces ie,

    NA1539*73M23P35 will be extracted as
    $result[0] = 'NA', $result[1] = '1539', $result[2] = '73',
    $result[3] = 'M', $result[4] = '23', $result[5] = 'P', $result[6] = '35'

    NA1539M23P35 will be extracted as
    $result[0] = 'NA', $result[1] = '1539', $result[2] = '',
    $result[3] = 'M', $result[4] = '23', $result[5] = 'P', $result[6] = '35'
*/
function code_extract($callnum)
{
    $result = array();
    $code_i = 0;
    $result[0] = '';

    $is_letter = true;

    // Replace 'OVERSIZE' in call number with 'OVSZ' in order to work with
    // following logic
    $callnum = str_replace('OVERSIZE', 'OVSZ', $callnum);

    $special = array('A/V', 'OVSZ');
    $special_num = array('0', '1');

    $callnum_s = str_replace($special, '', $callnum);
    $callnum = str_replace($special, $special_num, $callnum);

    // A special ID number
    if (is_digit($callnum[0])) {
        $result[0] .= $callnum[0];
    }

    for ($i = 0; $i < strlen($callnum_s); $i++) {
        $curr_char = $callnum_s[$i];

        if ($is_letter) {
            // if current char is a letter
            if (is_digit($curr_char)) {
                // the current char change from letter to digit,
                // so start extracting next section
                $is_letter = false;
                $code_i++;
                $result[$code_i] = '';
            }
        } else {
            // if current char is a digit
            if ($curr_char == '*') {
                // check for the special *, which is the decimal point,
                // if it is, advance to next section and ignore the *
                $code_i++;
                $result[$code_i] = '';
                continue;
            } elseif (!is_digit($curr_char)) {
                // the current char change from digit to letter,
                // so start extracting next section
                if ($code_i == 1) {
                    // the call number does not have decimal point, so set
                    // decimal as empty string, and advance to next section
                    $code_i++;
                    $result[2] = '';
                }

                $is_letter = true;
                $code_i++;
                $result[$code_i] = '';
            }
        }

        $result[$code_i] .= $curr_char;
    }

    return $result;
}

function is_digit($n)
{
    return ('0' <= $n && $n <= '9');
}

function is_alpha($n)
{
    return ('A' <= $n && $n <= 'Z');
}
