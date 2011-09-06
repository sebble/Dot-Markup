  = Dot Markup

 == Documentation

=== Extending Element Definitions

Nearly all elements in this markup can be extended with ``id`` and ``class`` attributes.  Although this markup has been designed to hide details not directly related to content there may be some situations where the standard tags are not enough.

<style>
.warning {
    background-color: #fee;
    padding: 1em;
    border: solid 2px #f00;
}
</style>

.warning This paragraph is important because it contains a warning: "Do not feed the ducks". and was created with the code ``{{{.warning This paragraph is...}}}``.

The previous paragraph was produced by adding the class name ``warning`` to the element, any element that can have classes added can also have an id with the exception of numbered list items.

|| Element || Markup !!
|| Paragraphs || ``{{{#id.class1.class2 This is a paragraph.}}}`` ||
|| Inline formatting || ``{{{##.classname This is a span##}}}`` ||
|| Inline elements || ``{{{[[tag#id.class:param1 param2 "param3"]]}}}`` ||
|| Block lines || ``{{{===.classname Heading 3 ===}}}`` ||
|| Block elements || [[[:::
{{{>>>.classname
  Blockquote with a class.
>>>}}}
:::]]] ||
|| List Items || ``{{{ *.classname List Item}}}`` ||
|| Full Lists || [[[:::
{{{#id.class
 * List Item 1
 * List Item 2}}}
:::]]] ||
|| Table rows || ``{{{|| First cell || ... || Last cell ||.rowclass}}}`` ||
|| Table cells || ``{{{||.class1 First cell ||.class2 Second Cell ||}}}`` ||
|| Full tables || ``{{{|| First row || ... ||.tableclass}}}`` ||

=== Using Special Groups

The inline group ``{{{&amp;lt;&amp;lt;&amp;lt; ... >>>}}}`` may be used to extend headings onto multiple lines in the source:

:::
{{{ === &amp;lt;&amp;lt;&amp;lt;My Heading Looks Nicer
   To Edit On Two Lines>>>
}}}
:::

The block group ``{{{[[[ ... ]]]}}}`` may be used in lists or tables where you want more content or just to use another block element:

:::
{{{ * [[[
This text will become a paragraph.

:::
And this will be 'preformatted'.
:::

Everything here is within the first list item.
]]]
}}}
:::

The literal group ``{{{{{{ ... }}}}}}`` can be used to protect code that may be interpreted as markup when this isn't desired.  This can be used inside ``{{{`` ... ``}}}`` to display example markup code.

=== Lists

Lists can be either ordered or unordered:

 # Ordered List Item
 # Ordered List Item

 * Unordered Item 1
 * Unordered Item 2

Lists can also contain other lists:

|| Markup || Result !!
|| [[[,,,
 # First
 #* A note
 # Second
 #* Another note
 #* Last one
 #: a bit more
 # Third
,,,]]] || [[[
 # First
 #* A note
 # Second
 #* Another note
 #* Last one
 #: a bit more
 # Third
]]] ||

and can also be continued after a child list by using the ``*:`` notation.

=== Tables

 == Reference

The following reference lists all inline, block, and special configured markups.  For fixed markups such as the table and lists, see the Documentation section.

=== Inline Formatting

|| Markup || Result !!
|| ``{{{**strong**}}}`` || **strong** ||
|| ``{{{''emphasis''}}}`` || ''emphasis'' ||
|| ``{{{``code``}}}`` || ``code`` ||
|| ``{{{--deleted--}}}`` || --deleted-- ||
|| ``{{{~~deleted~~}}}`` || ~~deleted~~ ||
|| ``{{{++inserted++}}}`` || ++inserted++ ||
|| ``{{{""quotation""}}}`` || ""quotation"" ||
|| ``{{{^^super^^script}}}`` || ^^super^^script ||
|| ``{{{__sub__script}}}`` || __sub__script ||
|| ``{{{,,sub,,script}}}`` || ,,sub,,script ||
|| ``{{{##span##}}}`` || ##span## ||


=== Inline Symbols

|| Markup || Result !!
|| ``{{{(c)}}}`` || (c) ||
|| ``{{{(C)}}}`` || (C) ||
|| ``{{{(r)}}}`` || (r) ||
|| ``{{{(R)}}}`` || (R) ||
|| ``{{{(tm)}}}`` || (tm) ||
|| ``{{{(TM)}}}`` || (TM) ||
|| ``{{{[TM]}}}`` || [TM] ||
|| ``{{{-}}}`` || - ||
|| ``{{{--}}}`` || -- ||
|| ``{{{...}}}`` || ... ||
|| ``{{{&amp;lt;&amp;lt;}}}`` || << ||
|| ``{{{>>}}}`` || >> ||
|| ``{{{^o^}}}`` || ^o^ ||
|| ``{{{^2}}}`` || ^2 ||
|| ``{{{^3}}}`` || ^3 ||
|| ``{{{1/2}}}`` || 1/2 ||
|| ``{{{1/4}}}`` || 1/4 ||
|| ``{{{3/4}}}`` || 3/4 ||
|| ``{{{5 x 5}}}`` || 5 x 5 ||
|| ``{{{2 / 3}}}`` || 2 / 3 ||
|| ``{{{+-}}}`` || +- ||
|| ``{{{!=}}}`` || != ||
|| ``{{{&amp;lt;=}}}`` || <= ||
|| ``{{{>=}}}`` || >= ||
|| ``{{{&amp;lt;--}}}`` || <-- ||
|| ``{{{-->}}}`` || --> ||
|| ``{{{&amp;lt;-->}}}`` || <--> ||
|| ``{{{a___b}}}`` || a___b ||
|| ``{{{£}}}`` || £ ||
|| ``{{{&}}}`` || & ||


=== Inline Tags

|| Markup || Result !!
|| ``{{{[[link:http://localhost Link Text "Title Text"]]}}}`` || [[link:http://localhost Link Text "Title Text"]] ||
|| ``{{{[[abbr Abbr. "Abbreviation"]]}}}`` || [[abbr Abbr. "Abbreviation"]] ||
|| ``{{{[[acr T.L.A. "Three Letter Acronym"]]}}}`` || [[acr T.L.A. "Three Letter Acronym"]] ||
|| ``{{{[[name:named-anchor Anchor Text "Title Text"]]}}}`` || [[name:named-anchor Anchor Text "Title Text"]] ||
|| ``{{{[[img:http://sebble.com/images/banner.gif|150 alternative text "Title Text"]]}}}`` || [[img:http://sebble.com/images/banner.gif|150 alternative text "Title Text"]] ||
|| ``{{{[[img:http://sebble.com/images/banner.gif|150,100 alternative text "Title Text"]]}}}`` || [[img:http://sebble.com/images/banner.gif|150,100 alternative text "Title Text"]] ||

=== Special Groups

|| Markup || Parsed || Block/Inline || Trim !!
|| ``{{{&amp;lt;script ... &amp;lt;/script>}}}`` || No || - || No ||
|| ``{{{&amp;lt;!-- ... -->}}}`` || No || - || No ||
|| ``{{{&amp;lt;style ... &amp;lt;/style>}}}`` || No || - || No ||
|| ``{{{{{{ ... }}}}}}`` || No || - || Yes ||
|| ``{{{&amp;lt;&amp;lt;&amp;lt; ... >>>}}}`` || Yes || Inline || Yes ||
|| ``{{{[[[ ... ]]]}}}`` || Yes || Block || Yes ||


=== Special Lines

|| Markup || HTML Tag || Structure !!
|| ``{{{=}}}`` to ``{{{======}}}`` || ``&amp;lt;h1>`` to ``&amp;lt;h6>`` || Headings 1 to 6 ||
|| ``{{{----}}}`` and longer || ``&amp;lt;hr />`` || Horizontal rule ||

=== Block Elements

|| Markup || HTML Tag || Parsed || Result !!
|| [[[,,,
"""
a large section of quoted text

a large section of quoted text
"""
,,,]]] || ``&amp;lt;blockquote>`` || Block || [[["""
a large section of quoted text

a large section of quoted text
"""]]] ||
|| [[[,,,
:::
  some
preformatted
    text(tm)
:::
,,,]]] || ``&amp;lt;pre>`` || Inline || [[[:::
  some
preformatted
    text(tm)
:::]]] ||
|| [[[,,,
```
  some
preformatted
    text(tm)
```
,,,]]] || ``&amp;lt;pre>`` || Inline || [[[```
  some
preformatted
    text(tm)
```]]] ||
|| [[[,,,
>>>
Written by Aenean dolor \\
[[mailto:aenean.dolor@malesuada.non 
aenean.dolor@malesuada.non]]
>>>
,,,]]] || ``&amp;lt;address>`` || Inline || [[[>>>
Written by Aenean dolor \\
[[mailto:aenean.dolor@malesuada.non 
aenean.dolor@malesuada.non]]
>>>]]] ||
|| [[[,,,
{{{,,,
  untouched
preformatted
    text(tm)
,,,}}}
,,,]]] || ``&amp;lt;pre>`` || - || [[[,,,
  untouched
preformatted
    text(tm)
,,,]]] ||

=== Lists


|| Markup || Result !!
|| [[[:::
{{{
 * List Item 1
 * List Item 2
}}}
:::]]] || [[[
 * List Item 1
 * List Item 2
]]] ||
|| [[[:::
{{{
 # List Item 1
 # List Item 2
}}}
:::]]] || [[[
 # List Item 1
 # List Item 2
]]] ||
|| [[[:::
{{{
 * List Item 1
 ** List Item 1a
 *: Item 1 continued
}}}
:::]]] || [[[
 * List Item 1
 ** List Item 1a
 *: Item 1 continued
]]] ||

=== Tables


|| Markup || Result !!
|| [[[:::
{{{
!! Table Heading !! Table Heading !!
}}}
:::]]] || [[[
!! Table Heading !! Table Heading !!
]]] ||
|| [[[:::
{{{
|| Table Heading || Table Heading !!
}}}
:::]]] || [[[
|| Table Heading || Table Heading !!
]]] ||
|| [[[:::
{{{
|| Table Cell || Table Cell ||
!! Table Cell || Table Cell ||
}}}
:::]]] || [[[
|| Table Cell || Table Cell ||
!! Table Cell || Table Cell ||
]]] ||
|| [[[:::
{{{
|| Table Head !! Table Head !!
!! Table Cell || Table Cell ||
!! Table Cell || Table Cell ||
|| Table Foot !! Table Foot !!
}}}
:::]]] || [[[
|| Table Head !! Table Head !!
!! Table Cell || Table Cell ||
!! Table Cell || Table Cell ||
|| Table Foot !! Table Foot !!
]]] ||

== Forms Extension

Forms are not something that have much to do with content, but when using ''Dot Markdown'' for simplifying basic web pages as well as writing articles a simple form would be very useful.  For this reason I have added some more definitions to 'complex' and 'liners' providing easy access to most form elements.

See the markup and form below for examples of the form elements.

<style>
fieldset {
  border-color: #49b;
  padding: 0 1em;
}
legend span {
  color: #666;
  font-family: georgia;
  font-style: italic;
}
legend span:before {
  content: " - ";
}
label {
  margin: 1.5em 0 0.2em 0;
  display: block;
}
label span {
  color: #999;
  font-family: georgia;
  font-style: italic;
  padding-left: 0em;
}
p.required label span {
  color: #900;
}
input[type="text"],
input[type="password"],
input[type="file"],
input[type="date"],
form textarea,
form select {
  margin: 0 0 1em 0;
  display: block;
  font-size: 1.2em;
  width: 99%;
  outline: none;
  border: solid 1px #999;
  background-color: #fff;
  border-radius: 5px;
  padding: 0.2em 0.3em;
  line-height: 1.2em;
}
input[type="password"] {
  color: #9cc;
  color: #49b;
}
form textarea {
  height: 12em;
  font-family: verdana;
}
p.required input {
    border-color: #900;
}

input[type="checkbox"],
input[type="radio"],
p.checkbox label,
input[type="checkbox"] + label,
input[type="radio"] + label {
  display: inline;
  line-height: 1.2em;
}
input[type="checkbox"],
input[type="radio"] {
  line-height: 1.2em;
  margin: 0 0.5em;
}
form h5 {
  font-family: verdana;
  color: #000;
  font-size: 1em;
  font-weight: bold;
}
form h3:before,
form h4:before,
form h5:before {
  content: none;
}
input[type="button"],
input[type="submit"],
input[type="reset"] {
  background: #fcfcfc;
  font-size: 1.1em;
  background-color: #fff;
  border-radius: 5px;
  border: solid 1px #999;
  background-color: #fff;
  border-radius: 5px;
  padding: 0.3em 0.7em;
  margin: 0 0.5em;
  cursor: pointer;
}
input[type="button"]:hover,
input[type="submit"]:hover,
input[type="reset"]:hover {
  background: #fcfcfc;
}
input[type="button"]:active,
input[type="submit"]:active,
input[type="reset"]:active {
  background: #eee;
}
form p.notes {
  font-size: 0.9em;
  color: #333;
}
</style>

=== Sample Form Markup 
:::
{{{
[[form:form_submit.php|post Form Legend ##quick description##]]
 
&amp;lt;!-- +++ Form Legend ## quick description ## -->

.notes This form has been been produced without any HTML being written. Isn't that awesome!

[[text:form_text Username ##username## "e.g., sebble"]]

[[password:form_password Password ##minimum 5 characters##]]

.required [[text:form_required Required ##required##]]

=== Multi-choice question? ##hint##
[[checkbox:form_chk|chk1 Choice 1 "This is the first choice"]]
[[checkbox:form_chk|chk2 Choice 2 "This is the first choice"]]
[[checkbox:form_chk|chk3 Choice 3 "This is the first choice"]]

=== Single answer question? ##hint##
[[radio:form_radio|rad1 Choice 1 "This is the first choice"]]
[[radio:form_radio|rad2 Choice 2 "This is the first choice"]]
[[radio:form_radio|rad3 Choice 3 "This is the first choice"]]
[[textarea:form_ta Textarea Input ##- something tasty##]]

[[file:form_file File Select ##upload your face##]]

[[text:form_date Date Input ##enter a date as YYYY-MM-DD## "YYYY-MM-DD"]]

.notes Thank you for completing this short survey.

[[reset Clear Form]] [[submit Submit Form]]

[[form:end]]
}}}
:::

<script>
var i = document.createElement("input");
  i.setAttribute("type", "date");
  if (i.type == "text") {
    alert("No native date picker.");
  } else {
    //alert("We have a native date picker.");
  }
</script>

=== Sample Form

[[form:form_submit.php|post Form Legend ##quick description##]]
 
<!--+++ Form Legend ## quick description ##-->

.notes This form has been been produced without any HTML being written. Isn't that awesome!

[[text:form_text Username ##username## "e.g., sebble"]]

[[password:form_password Password ##minimum 5 characters##]]

.required [[text:form_required Required ##required##]]

=== Multi-choice question? ##hint##
[[checkbox:form_chk|chk1 Choice 1 "This is the first choice"]]
[[checkbox:form_chk|chk2 Choice 2 "This is the first choice"]]
[[checkbox:form_chk|chk3 Choice 3 "This is the first choice"]]

=== Single answer question? ##hint##
[[radio:form_radio|rad1 Choice 1 "This is the first choice"]]
[[radio:form_radio|rad2 Choice 2 "This is the first choice"]]
[[radio:form_radio|rad3 Choice 3 "This is the first choice"]]
[[textarea:form_ta Textarea Input ##- something tasty##]]

[[file:form_file File Select ##upload your face##]]

[[text:form_date Date Input ##enter a date as YYYY-MM-DD## "YYYY-MM-DD"]]

.notes Thank you for completing this short survey.

[[reset Clear Form]] [[submit Submit Form]]

[[form:end]]

=== Current State of Form Extension

Unfortunately there are a couple of bugs, errors, or hacks involved in this extension.  See the ``{{{[[form:end]]}}}`` tag, that is not an inline element but in fact a 'liner'.

Secondly, the form open tag itself ''is'' an inline element, which means that it will be incorrectly wrapped in ``&amp;lt;p>`` tags, given the need for parameters (action and method), the only markup feature available was the complex inline tag.

Another minor problem is with the id attribute of the form input elements, these are set by the first parameter (for use in the ``&amp;lt;label>``s) and setting any id by the #-hash markup will assign two ids.

Lastly, a problem  also experienced by all other complex inline elements: if no value is given for say 'placeholder' then an empty placeholder will be set.  Decisions to instead put "BIGRANDOMMARKER" and then remove all ``\b[^\s]*BIGRANDOMMARKER[^\s]*\b`` are yet undecided due to the possible bugs they could create.

To finish on a solution, maybe the opening and closing HTML for a form should be manually written, and an ignore group added to avoid wrapping the tags in extra paragraphs.













\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
