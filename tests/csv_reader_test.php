<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\adduser\tests;

use phpbbmodders\adduser\csv_reader;

/**
 * Tests \phpbbmodders\adduser\csv_reader
 */
class csv_reader_test extends \phpbb_test_case
{
	/** @var csv_reader */
	protected $reader;

	protected function setUp(): void
	{
		parent::setUp();

		$this->reader = new csv_reader();
	}

	public function test_header_in_any_order_with_optional_columns()
	{
		$result = $this->reader->parse("email,username,lang\nalice@example.com,alice,de\nbob@example.com,bob,\n", 10);

		$this->assertNull($result['error']);
		$this->assertSame(['email', 'username', 'lang'], $result['columns']);
		$this->assertCount(2, $result['rows']);
		$this->assertSame(2, $result['rows'][0]['row']);
		$this->assertSame([
			'username'	=> 'alice',
			'email'		=> 'alice@example.com',
			'password'	=> '',
			'lang'		=> 'de',
			'birthday'	=> '',
		], $result['rows'][0]['values']);
		$this->assertSame('', $result['rows'][1]['values']['lang']);
		$this->assertNull($result['rows'][1]['error']);
	}

	public function test_semicolons_bom_crlf_and_header_case()
	{
		$result = $this->reader->parse("\xEF\xBB\xBFUserName;Email\r\nzoë;zoe@example.com\r\n", 10);

		$this->assertNull($result['error']);
		$this->assertSame('zoë', $result['rows'][0]['values']['username']);
		$this->assertSame('zoe@example.com', $result['rows'][0]['values']['email']);
	}

	public function test_quoted_fields_keep_separators_and_doubled_quotes()
	{
		$result = $this->reader->parse("username,email,password\n\"Smith, J\",j@example.com,\"pa\"\"ss\\\"\n", 10);

		$this->assertNull($result['error']);
		$this->assertSame('Smith, J', $result['rows'][0]['values']['username']);
		$this->assertSame('pa"ss\\', $result['rows'][0]['values']['password']);
	}

	public function test_blank_rows_are_skipped_and_row_numbers_follow_the_file()
	{
		$result = $this->reader->parse("username,email,\n\n,,\ncarol,carol@example.com,\n", 10);

		$this->assertNull($result['error']);
		$this->assertCount(1, $result['rows']);
		$this->assertSame(4, $result['rows'][0]['row']);
	}

	public function test_extra_fields_are_a_row_error()
	{
		$result = $this->reader->parse("username,email\ndave,dave@example.com,surplus\nerin,erin@example.com,\n", 10);

		$this->assertNull($result['error']);
		$this->assertSame(['ADDUSER_CSV_TOO_MANY_FIELDS', 2], $result['rows'][0]['error']);
		$this->assertNull($result['rows'][1]['error']);
	}

	public static function file_error_data()
	{
		return [
			'empty'				=> ["  \n", ['ADDUSER_CSV_EMPTY']],
			'not utf-8'			=> ["username,email\n\xE9ric,eric@example.com\n", ['ADDUSER_CSV_NOT_UTF8']],
			'unknown column'	=> ["username,email,phone\n", ['ADDUSER_CSV_UNKNOWN_COLUMN', 'phone', 'username, email, password, lang, birthday']],
			'duplicate column'	=> ["username,email,email\n", ['ADDUSER_CSV_DUPLICATE_COLUMN', 'email']],
			'missing column'	=> ["username,password\nfrank,secret\n", ['ADDUSER_CSV_MISSING_COLUMN', 'email']],
			'no data rows'		=> ["username,email\n\n", ['ADDUSER_CSV_NO_ROWS']],
			'too many rows'		=> ["username,email\na,a@example.com\nb,b@example.com\nc,c@example.com\n", ['ADDUSER_CSV_TOO_MANY_ROWS', 2]],
		];
	}

	/**
	 * @dataProvider file_error_data
	 */
	public function test_file_errors($contents, $expected)
	{
		$result = $this->reader->parse($contents, 2);

		$this->assertSame($expected, $result['error']);
		$this->assertSame([], $result['rows']);
	}
}
