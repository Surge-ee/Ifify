# Ifify

Ifify is a plugin for [ExpressionEngine](http://ellislab.com/expressionengine) that allows any other plugin's methods to be turned into pseudo-conditionals.

## Motivation & Example

Say you are creating a template that outputs a series of divs. You want divs to be paired together such that they are each inside of a parent div that forms a row.

	<div class="row">
		<div>Content 1</div>
		<div>Content 2</div>
	</div>

	<div class="row">
		<div>Content 3</div>
		<div>Content 4</div>
	</div>

For two rows like the above, the solution is simple:

	<div class="row">
	{exp:channel:entries channel="some_channel" limit="4"}
		<div>Content {count}</div>

	{if count == 2}
	</div>
	<div class="row">
	{/if}

	{/exp:channel:entries}
	</div>

However, as soon as you make the total number of elements variable, you will need to modify it to output the closing and opening divs after every odd element. One way to implement this is to use a modulo plugin to check if `{count} mod 2` is 1.

	<div class="row">
	{exp:channel:entries channel="some_channel"}
		<div>Content {count}</div>

	{if '{exp:surgeree:modulo numerator="{count}" denominator="2"}' == 1}
	</div>
	<div class="row">
	{/if}

	{/exp:channel:entries}
	</div>

That code does not work. If you dig in, you'll find that the `{count}` variable is being passed in to the modulo plugin unparsed. Nesting the variable inside a plugin inside an if is one too many levels for the EE parser to handle. You can spend hours trying to figure out a workaround, often falling back on switching around single and double quotes, or throwing things into embed and passing the count in (ouch! bad for performance!). This plugin provides you a solution by removing the one-too-many level of nesting. Observe:


	<div class="row">
	{exp:channel:entries channel="some_channel"}
		<div>Content {count}</div>

	{exp:ifify:surgeree method="modulo" numerator="{count}" denominator="2" truthy="1"}
	</div>
	<div class="row">
	{/exp:ifify:surgeree}

	{/exp:channel:entries}
	</div>

This succesfully passes the value of `{count}` to the `Surgeree::modulo` method, checks the return value of that plugin against the "truthy" value, and either returns the contents of the tag pair if true, or nothing if false.

## Usage

	{exp:ifify:<plugin_name> [<plugin_parameters>] truthy="<value_to_show_content_on>" [method="<method_name>"]}

- `<plugin_name>` This is simply the name of the plugin you wish to make a conditional out of.
- `<plugin_parameters>` *Optional.* These are the parameters you would normally supply if you were calling the plugin directly. In the example from the section above, they are the "numerator" and "denominator" parameters.
- `<value_to_show_content_on>` Ifify will run the plugin method for you, but it needs to have a value to compare the plugin's output with to determine whether or not to display its contents. In other words, what would you put on the other side of the `==` if this were an `{if}` conditional.
- `<method_name>` *Optional.* If the plugin is normally called as `{exp:plugin:method}`, this would be the "method" part. Some plugins are called as `{exp:plugin}`, in which case you can omit the method parameter entirely.
