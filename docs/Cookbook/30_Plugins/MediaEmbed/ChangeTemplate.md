## Change the template used to render embedded content

`add()` returns the tag that is associate with the newly-added media site. You can change its default template to change the way the embedded content is displayed. In the following example, we put YouTube videos inside of a special `<div>`.

```php
$configurator = new s9e\TextFormatter\Configurator;

$tag = $configurator->MediaEmbed->add('youtube');

$tag->template = '<div class="embed-youtube">'
               . $tag->template
               . '</div>';

// Get an instance of the parser and the renderer
extract($configurator->finalize());

$text = 'http://www.youtube.com/watch?v=-cEzsCAzTak';
$xml  = $parser->parse($text);
$html = $renderer->render($xml);

echo $html;
```
```html
<div class="embed-youtube"><div data-s9e-mediaembed="youtube" style="display:inline-block;width:100%;max-width:640px"><div style="overflow:hidden;position:relative;padding-bottom:56.25%"><iframe allowfullscreen="" scrolling="no" style="border:0;height:100%;left:0;position:absolute;width:100%" src="//www.youtube.com/embed/-cEzsCAzTak"></iframe></div></div></div>
```
