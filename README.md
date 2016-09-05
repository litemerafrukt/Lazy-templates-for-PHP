# Ltpl - Lazy templates
Very simple templating for PHP. A kind of boilerplate. Add stuff you
need when you need it. Think KISS. But mostly skip it, there are
good and mature alternetives :)

Use {@somekeyname} in your template html file to substitute with
a value from $key => $value array.
 
Use:
{@each:subtemplatekeyname}
-- subtemplate with a nested $key => $value array--
{@endeach:subtemplatekeyname}
to make for example a table from subarrays that form rows. each-endeach
doesn't support nesting, so no each-endeach within an each-endeach.

The subtemplate is written in the same
template document between {@each:???} and {@endeach:???}.

Written for an assignment for a course at
Stockholm University.

I took ideas and knowledge from this blog:
http://www.broculos.net/2008/03/how-to-make-simple-html-template-engine.html#.V7f9H7XbvAW
and some ideas from FlightPHP.

TODO: a render function that accepts a string as template instead of a fileneame.
