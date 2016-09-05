<?php
/********************************************************************
 * Ltpl - Lazy templates
 * Very simple templating. A kind of boilerplate. Add stuff you
 * need when you need it. Think KISS. But mostly skip it, there are
 * good and mature alternetives :)
 *
 * Use {@somekeyname} in your template html file to substitute with
 * a value from $key => $value array.
 *
 * Use:
 * {@each:subtemplatekeyname}
 * -- subtemplate with a nested $key => $value array--
 * {@endeach:subtemplatekeyname}
 * to make for example a table from subarrays that form rows. each-endeach
 * doesn't support nesting, so no each-endeach within an each-endeach.
 *
 * The subtemplate is written in the same
 * template document between {@each:???} and {@endeach:???}.
 *
 * Written for an assignment for a course at
 * Stockholm University.
 *
 * I took ideas and knowledge from this blog:
 * http://www.broculos.net/2008/03/how-to-make-simple-html-template-engine.html#.V7f9H7XbvAW
 * and some ideas from FlightPHP.
 *
 *
 * TODO: a render function that accepts a string as template instead of a fileneame.
 *
********************************************************************/

/**
 * Class: Ltpl
 *
 * This class is used as a kind of namespace for the template renderer.
 * Only static functions, not meant to be instantiated.
 */
class Ltpl
{
    /**
     * Renders a flat array on the template that is in $strTemplate.
     *
     * Used for rendering a single row in a multidimensional array.
     *
     * @param  string $strTemplate - the template with {@key} replace tokens
     * @param  array $rowArray - a flat array with key-value pairs where key maps to template
     *
     * @return string - the rendered template
     */
    private static function renderRow($strTemplate, $rowArray = [])
    {
        foreach ($rowArray as $key => $value) {
            $replaceToken = "{@$key}";
            $strTemplate = str_replace($replaceToken, $value, $strTemplate);
        }
        return $strTemplate;
    }

    /**
     * Render a multidimensional array. Uses the template in $strTemplate to
     * map key-value pairs row by row. Concatenates all rows with a newline
     * for prettier html.
     *
     * @param  string $strTemplate
     * @param  array $array
     *
     * @return string - all arrays in $array rendered in $strTemplate
     */
    private static function renderArray($strTemplate, $array = [])
    {
        $renderedArray = [];

        foreach ($array as $row) {

            $renderedArray[] = Ltpl::renderRow($strTemplate, $row);
        }

        return implode("\n", $renderedArray);
    }

    /**
     * Insert a value in template based on position of key in
     * template.
     *
     * @param  string $key - the key to search for in the template
     * @param  string $value - the value to replace key in template with
     * @param  string $strTemplate - the template
     *
     * @return
     */
    private static function insertValue($key, $value, $strTemplate)
    {
        //Check if value is an array
        if (is_array($value)) {

            // Cut out the piece that is between
            // {@each:$key} and {@endeach:$key}
            $subTemplateStart = strpos($strTemplate, "{@each:$key}");
            $subTemplateEnd = strpos($strTemplate, "{@endeach:$key}");
            if ($subTemplateStart === false or $subTemplateEnd === false) {
                // Probably not the the best idea to die here but lets do that anyway
                die('Major failure in template engine!');
            }

            $subTemplate = substr(
                $strTemplate,
                $subTemplateStart + strlen("{@each:$key}"),
                $subTemplateEnd - $subTemplateStart - strlen("{@each:$key}")
            );
            // use this as a template
            //
            // loop $value array and create one new
            // string for each key-value pair in array
            $subTemplate = Ltpl::renderArray($subTemplate, $value);

            // replace the piece in template with the new string
            return preg_replace(
                "/{@each:$key}[\\s\\S]*?{@endeach:$key}/",
                $subTemplate,
                $strTemplate
            );
        }
        // Not an array, puh!, then it's simple.
        $replaceToken = "{@$key}";

        return str_replace($replaceToken, $value, $strTemplate);
    }

    /**
     * Helper function to render an associative array in templates.
     * Maps an associative array to a new multidimensional array that is formatted
     * for output in {@each:???} <!-- your subtemplate --> {@endeach:???}.
     *
     * example:
     * ---------------------------------------------------------------------------
     *  echo Ltpl::render('superglobals.ltpl.html', [
     *      'serverglobal' => Ltpl::mapArray('serverkey', 'servervalue', $_SERVER)
     *  ]);
     * ---------------------------------------------------------------------------
     * maps the $_SERVER array so you can use it in this subtemplate:
     * ---------------------------------------------------------------------------
     *  {@each:serverglobal}
     *   <tr>
     *       <td>{@serverkey}</td>
     *       <td>{@servervalue}</td>
     *   </tr>
     *   {@endeach:serverglobal}
     * ---------------------------------------------------------------------------
     *
     * @param  string $templateKey - the key in the template that the key in array maps to
     * @param  string $templateValue - the value associated with template key
     * @param  array  $array - array of key-value pairs that you want to render in a subtemplate
     *
     * @return array - a new 2 dimensional array with input array key values mapped to template key values
     */
    public static function mapArray($templateKey, $templateValue, $array)
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $newArray[] = [$templateKey => $key, $templateValue => $value];
        }

        return $newArray;
    }

    /**
     * Renders a template with values from input array.
     *
     * Looks at each key value pair in input array. If key is found in template
     * (eg {@key}) then it substitutes the key with the value from the array.
     *
     * If the value is an array it looks for a subtemplate with the name from the key
     * to use for rendering (eg {@each:key} <!-- some stuff --> {@endeach:key} )
     *
     * example:
     * php:
     * ---------------------------------------------------------------------------
     *  echo Ltpl::render('answer.ltpl.html', ['answer' => '42']);
     * ---------------------------------------------------------------------------
     * template:
     * ---------------------------------------------------------------------------
     *  <body>
     *      <div>{@answer}</div>
     *  </body>
     * ---------------------------------------------------------------------------
     * renders/outputs:
     * ---------------------------------------------------------------------------
     *  <body>
     *      <div>42</div>
     *  </body>
     * ---------------------------------------------------------------------------
     *
     *
     * @param  string $template - the template file name
     * @param  string $values - the array with key-value pairs that should be inserted in the
     *                          template.
     *
     * @return string - the template as a string with keys substituted with values
     */
    public static function render($template, $values = [])
    {
        if (!file_exists($template)) {
            return "Error loading template file: $template";
        }

        $content = file_get_contents($template);

        foreach ($values as $key => $value) {
            $content = Ltpl::insertValue($key, $value, $content);
        }

        return $content;
    }
}
