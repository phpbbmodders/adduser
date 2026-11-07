<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\adduser;

/**
 * Reads the CSV file of the "Add users from CSV" page.
 *
 * The first row names the columns, in any order. username and email are
 * required; password, lang and birthday are optional. The file must be UTF-8
 * (a byte order mark is dropped). The separator is a comma or a semicolon,
 * whichever the header row has more of.
 *
 * This class only splits the file into rows. Checking the values is left to
 * phpBB's own validation in the ACP module.
 */
class csv_reader
{
	/** @var string[] Every column the file may have */
	const COLUMNS = ['username', 'email', 'password', 'lang', 'birthday'];

	/** @var string[] Columns the file must have */
	const REQUIRED_COLUMNS = ['username', 'email'];

	/**
	 * Split CSV text into rows keyed by column name.
	 *
	 * @param string $contents The whole file
	 * @param int    $max_rows Most data rows allowed, not counting the header row
	 * @return array [
	 *     'error'   => array|null  File-level error: a language key followed by its arguments, or null
	 *     'columns' => string[]    Column names in file order
	 *     'rows'    => array[]     One entry per non-blank data row: [
	 *         'row'    => int         Row number in the file, the header row being row 1
	 *         'values' => string[]    Every entry of COLUMNS mapped to its trimmed value ('' when absent)
	 *         'error'  => array|null  Row-level error, as above, or null
	 *     ]
	 * ]
	 */
	public function parse($contents, $max_rows)
	{
		$result = ['error' => null, 'columns' => [], 'rows' => []];

		// Excel adds a byte order mark when saving "CSV UTF-8"
		if (strncmp($contents, "\xEF\xBB\xBF", 3) === 0)
		{
			$contents = substr($contents, 3);
		}

		if (trim($contents) === '')
		{
			$result['error'] = ['CSV_EMPTY'];
			return $result;
		}

		if (!preg_match('//u', $contents))
		{
			$result['error'] = ['CSV_NOT_UTF8'];
			return $result;
		}

		$first_line = strtok($contents, "\r\n");
		$separator = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';

		$handle = fopen('php://temp', 'r+');
		fwrite($handle, $contents);
		rewind($handle);

		$header = $this->read_record($handle, $separator);
		$columns = [];

		// Column name by field position; null for the blank header cells
		// spreadsheets leave after the last column
		$positions = [];

		foreach ($header as $index => $name)
		{
			$name = strtolower(trim((string) $name));

			if ($name === '')
			{
				$positions[$index] = null;
				continue;
			}

			if (!in_array($name, self::COLUMNS, true))
			{
				fclose($handle);
				$result['error'] = ['CSV_UNKNOWN_COLUMN', $name, implode(', ', self::COLUMNS)];
				return $result;
			}

			if (in_array($name, $columns, true))
			{
				fclose($handle);
				$result['error'] = ['CSV_DUPLICATE_COLUMN', $name];
				return $result;
			}

			$columns[] = $name;
			$positions[$index] = $name;
		}

		foreach (self::REQUIRED_COLUMNS as $name)
		{
			if (!in_array($name, $columns, true))
			{
				fclose($handle);
				$result['error'] = ['CSV_MISSING_COLUMN', $name];
				return $result;
			}
		}

		$result['columns'] = $columns;
		$row_number = 1;

		while (($record = $this->read_record($handle, $separator)) !== false)
		{
			$row_number++;
			$fields = array_map('trim', array_map('strval', $record));

			// Skip blank rows, including the ",,," rows spreadsheets leave behind
			if (implode('', $fields) === '')
			{
				continue;
			}

			if (count($result['rows']) >= $max_rows)
			{
				fclose($handle);
				$result['error'] = ['CSV_TOO_MANY_ROWS', $max_rows];
				$result['rows'] = [];
				return $result;
			}

			$values = array_fill_keys(self::COLUMNS, '');
			$error = null;

			foreach ($fields as $index => $value)
			{
				if (isset($positions[$index]))
				{
					$values[$positions[$index]] = $value;
				}
				else if ($value !== '')
				{
					$error = ['CSV_TOO_MANY_FIELDS', count($columns)];
				}
			}

			$result['rows'][] = ['row' => $row_number, 'values' => $values, 'error' => $error];
		}

		fclose($handle);

		if (!count($result['rows']))
		{
			$result['error'] = ['CSV_NO_ROWS'];
		}

		return $result;
	}

	/**
	 * Read one CSV record, following RFC 4180: quotes are escaped by doubling
	 * them, never by a backslash.
	 *
	 * @param resource $handle    Open stream
	 * @param string   $separator Field separator
	 * @return array|false Fields of the record, or false at the end of the stream
	 */
	protected function read_record($handle, $separator)
	{
		return fgetcsv($handle, 0, $separator, '"', '');
	}
}
