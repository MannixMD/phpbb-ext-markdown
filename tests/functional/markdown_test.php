<?php

/**
 * Markdown extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
 * @copyright 2019 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\markdown\tests\functional;

/**
 * @group functional
 */
class markdown_test extends \phpbb_functional_test_case
{
	use functional_test_case_trait;

	protected function setUp(): void
	{
		parent::setUp();
		$this->login();
		$this->add_lang_ext('alfredoramos/markdown', [
			'posting'
		]);
	}

	public function test_post_reply()
	{
		$crawler = self::request('GET', sprintf(
			'posting.php?mode=reply&f=2&t=1&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();

		$this->assertSame(1, $crawler->filter('.markdown-status')->count());
		$this->assertSame(
			'/app.php/help/markdown',
			$crawler->filter('.markdown-status > a')->attr('href')
		);
		$this->assertTrue($form->has('disable_markdown'));
	}

	public function test_post_markdown()
	{
		$markdown = <<<EOT
Code:

```php
echo 'message';
```

Inline `code`
EOT;
		$post = $this->create_topic(
			2,
			'Markdown functional test 1',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		if (version_compare(PHP_VERSION, '7.3.0', '>='))
		{
			$expected = <<<EOT
<p>Code:</p>

<div class="codebox"><p>Code: <a href="#" onclick="selectCode(this); return false;">Select all</a></p><pre><code>echo 'message';</code></pre></div>

<p>Inline <code>code</code></p>
EOT;
		}
		else
		{
			$expected = <<<EOT
<p>Code:</p>

<div class="codebox">
<p>Code: <a href="#" onclick="selectCode(this); return false;">Select all</a></p>
<pre><code>echo 'message';</code></pre>
</div>

<p>Inline <code>code</code></p>
EOT;
		}

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$this->assertStringContainsString($expected, $result->html());
	}

	public function test_private_message()
	{
		$markdown = <<<EOT
Code:

```php
echo 'message';
```

Inline `code`
EOT;
		$private_message = $this->create_private_message(
			'Markdown private message test 1',
			$markdown,
			[2]
		);

		$crawler = self::request('GET', sprintf(
			'ucp.php?i=pm&mode=view&p=%d&sid=%s',
			$private_message,
			$this->sid
		));

		if (version_compare(PHP_VERSION, '7.3.0', '>='))
		{
			$expected = <<<EOT
<p>Code:</p>

<div class="codebox"><p>Code: <a href="#" onclick="selectCode(this); return false;">Select all</a></p><pre><code>echo 'message';</code></pre></div>

<p>Inline <code>code</code></p>
EOT;
		}
		else
		{
			$expected = <<<EOT
<p>Code:</p>

<div class="codebox">
<p>Code: <a href="#" onclick="selectCode(this); return false;">Select all</a></p>
<pre><code>echo 'message';</code></pre>
</div>

<p>Inline <code>code</code></p>
EOT;
		}

		$result = $crawler->filter(sprintf(
			'#post-%d .content',
			$private_message
		));

		$this->assertStringContainsString($expected, $result->html());
	}

	public function test_simple_table()
	{
		$markdown = <<<EOT
| Header 1 | Header 2 |
|----------|----------|
| Cell 1   | Cell 2   |
EOT;

		$post = $this->create_topic(
			2,
			'Markdown tables test 1',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		if (version_compare(PHP_VERSION, '7.3.0', '>='))
		{
			$expected = '<table class="markdown"><thead><tr><th>Header 1</th><th>Header 2</th></tr></thead><tbody><tr><td>Cell 1</td><td>Cell 2</td></tr></tbody></table>';
		}
		else
		{
			$expected = <<<EOT
<table class="markdown">
<thead><tr>
<th>Header 1</th>
<th>Header 2</th>
</tr></thead>
<tbody><tr>
<td>Cell 1</td>
<td>Cell 2</td>
</tr></tbody>
</table>
EOT;
		}

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$this->assertSame(1, $crawler->filter('table')->count());
		$this->assertStringContainsString($expected, $result->html());
	}

	public function test_compact_table()
	{
		$markdown = <<<EOT
Header 1|Header 2
-|-
Cell 1|Cell 2
EOT;

		$post = $this->create_topic(
			2,
			'Markdown tables test 2',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		if (version_compare(PHP_VERSION, '7.3.0', '>='))
		{
			$expected = '<table class="markdown"><thead><tr><th>Header 1</th><th>Header 2</th></tr></thead><tbody><tr><td>Cell 1</td><td>Cell 2</td></tr></tbody></table>';
		}
		else
		{
			$expected = <<<EOT
<table class="markdown">
<thead><tr>
<th>Header 1</th>
<th>Header 2</th>
</tr></thead>
<tbody><tr>
<td>Cell 1</td>
<td>Cell 2</td>
</tr></tbody>
</table>
EOT;
		}

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$this->assertSame(1, $crawler->filter('table')->count());
		$this->assertStringContainsString($expected, $result->html());
	}

	public function test_table_text_aligntment()
	{
		$markdown = <<<EOT
| Left | Center | Right |
|:-----|:------:|------:|
|   x  |    x   |   x   |
EOT;

		$post = $this->create_topic(
			2,
			'Markdown tables test 3',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		if (version_compare(PHP_VERSION, '7.3.0', '>='))
		{
			$expected = '<table class="markdown"><thead><tr><th style="text-align:left">Left</th><th style="text-align:center">Center</th><th style="text-align:right">Right</th></tr></thead><tbody><tr><td style="text-align:left">x</td><td style="text-align:center">x</td><td style="text-align:right">x</td></tr></tbody></table>';
		}
		else
		{
			$expected = <<<EOT
<table class="markdown">
<thead><tr>
<th style="text-align:left">Left</th>
<th style="text-align:center">Center</th>
<th style="text-align:right">Right</th>
</tr></thead>
<tbody><tr>
<td style="text-align:left">x</td>
<td style="text-align:center">x</td>
<td style="text-align:right">x</td>
</tr></tbody>
</table>
EOT;
		}

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$this->assertSame(1, $crawler->filter('table')->count());
		$this->assertStringContainsString($expected, $result->html());
	}

	public function test_block_spoiler()
	{
		$markdown = <<<EOT
>! Spoiler text
> Another line
EOT;

		$post = $this->create_topic(
			2,
			'Markdown spoilers test 1',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		$expected = <<<EOT
<details class="spoiler markdown"><p>Spoiler text<br>
Another line</p></details>
EOT;

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$this->assertSame(1, $crawler->filter('.spoiler')->count());
		$this->assertStringContainsString($expected, $result->html());
	}

	public function test_inline_spoiler()
	{
		$markdown = <<<EOT
This is a Reddit-style >!spoiler!<.
This is a Discord-style ||spoiler||.
EOT;

		$post = $this->create_topic(
			2,
			'Markdown spoilers test 1',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		$expected = <<<EOT
<p>This is a Reddit-style <span class="spoiler markdown" onclick="removeAttribute('style')" style="background:#444;color:transparent">spoiler</span>.<br>
This is a Discord-style <span class="spoiler markdown" onclick="removeAttribute('style')" style="background:#444;color:transparent">spoiler</span>.</p>
EOT;

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$this->assertSame(2, $crawler->filter('.spoiler')->count());
		$this->assertStringContainsString($expected, $result->html());
	}

	public function test_task_list()
	{
		$markdown = <<<EOT
- [x] Task 1
	- [x] Task 1.1
- [ ] Task 2
EOT;

		$post = $this->create_topic(
			2,
			'Markdown task list test 1',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$list = $result->filter('ul.markdown');
		$items = $list->filter('li[data-task-id]');

		$this->assertSame(2, $list->count());
		$this->assertSame(3, $items->count());

		if (version_compare(PHP_VERSION, '7.3.0', '>='))
		{
			$expected = <<<EOT
<ul class="markdown"><li data-task-id="..." data-task-state="checked"><input data-task-id="..." type="checkbox" checked disabled> Task 1
	<ul class="markdown"><li data-task-id="..." data-task-state="checked"><input data-task-id="..." type="checkbox" checked disabled> Task 1.1</li></ul></li>
<li data-task-id="..." data-task-state="unchecked"><input data-task-id="..." type="checkbox" disabled> Task 2</li></ul>
EOT;
		}
		else
		{
			$expected = <<<EOT
<ul class="markdown">
<li data-task-id="..." data-task-state="checked">
<input data-task-id="..." type="checkbox" checked disabled> Task 1
	<ul class="markdown"><li data-task-id="..." data-task-state="checked">
<input data-task-id="..." type="checkbox" checked disabled> Task 1.1</li></ul>
</li>
<li data-task-id="..." data-task-state="unchecked">
<input data-task-id="..." type="checkbox" disabled> Task 2</li>
</ul>
EOT;
		}

		$html = $this->task_id_placeholder($result->html());
		$expected = $this->task_id_placeholder($expected);

		$this->assertStringContainsString($expected, $html);
	}

	public function test_header_slugs()
	{
		$markdown = <<<EOT
# Lorem ipsum
## Pellentesque odio felis
### Vivamus eu nisl
#### Proin egestas ornare
##### Phasellus eu luctus lectus
###### Pellentesque eleifend feugiat
EOT;

		$post = $this->create_topic(
			2,
			'Markdown header slugs test 1',
			$markdown
		);

		$crawler = self::request('GET', sprintf(
			'viewtopic.php?t=%d&sid=%s',
			$post['topic_id'],
			$this->sid
		));

		$result = $crawler->filter(sprintf(
			'#post_content%d .content',
			$post['topic_id']
		));

		$headers = $result->filter('.markdown');
		$this->assertSame(6, $headers->count());

		for ($i = 1; $i <= 6; $i++) {
			$header = $result->filter(sprintf('h%d', $i));
			$this->assertSame(1, $header->count());
		}

		$expected = <<<EOT
<h1 class="markdown" id="lorem-ipsum">Lorem ipsum</h1>
<h2 class="markdown" id="pellentesque-odio-felis">Pellentesque odio felis</h2>
<h3 class="markdown" id="vivamus-eu-nisl">Vivamus eu nisl</h3>
<h4 class="markdown" id="proin-egestas-ornare">Proin egestas ornare</h4>
<h5 class="markdown" id="phasellus-eu-luctus-lectus">Phasellus eu luctus lectus</h5>
<h6 class="markdown" id="pellentesque-eleifend-feugiat">Pellentesque eleifend feugiat</h6>
EOT;

		$this->assertStringContainsString($expected, $result->html());
	}

	private function task_id_placeholder($html = '', $placeholder = '...')
	{
		if (empty($html))
		{
			return '';
		}

		return preg_replace('#(?<=data-task-id=")(\w+)(?=")#', $placeholder, $html);
	}
}
