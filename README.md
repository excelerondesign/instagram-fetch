# Instagram Fetch

A simple MODX snippet to get images from instagram using the Instagram Basic Display API.

## How to use

-   Follow these instructions to get your Instagram Basic Display access token [link](https://github.com/adrianengine/jquery-spectragram/wiki/Register-on-Instagram-Basic-Display-API-and-get-an-Access-Token)
-   Add the token as a system setting named `instagram_access_token`
-   Create a directory inside of `public_html/core/` called `classes`
-   Upload the file inside of `scripts/` to `public_html/core/classes/` as `instagram.class.php`
-   Create a snippet with the following code inside

```php
<?php
if (!$modx->loadClass('Instagram', MODX_CORE_PATH .'classes/', true, true)) {
    die('Could not load class');
}
$token = $modx->getOption('instagram_access_token');

$Instagram = new Instagram($modx, $token);

$tpl = $modx->getOption('tpl', $scriptProperties, '');

$max = $modx->getOption('max', $scriptProperties, 8);

return $Instagram->getPhotos($tpl, $max);
```

## Options

| Option | Default | Description                         |
| ------ | ------- | ----------------------------------- |
| tpl    |         | Template for each Instagram post    |
| max    | 8       | Maximum amount of posts to generate |

## Placeholders

| Title   | Placeholder | Description                                                                                  |
| ------- | ----------- | -------------------------------------------------------------------------------------------- |
| Type    | type        | Type of media (Image, Video, Carousel) useful for debugging in case Facebook changes the API |
| Source  | src         | Link to the image source                                                                     |
| Link    | link        | The direct link to the Instagram post                                                        |
| Caption | caption     | Associated post caption                                                                      |
| Index   | idx         | 1-based index variable                                                                       |

## Example

```php
<ul data-instagram-feed>
    [[Instagram?
        &tpl=`instagram-item`
        &max=`6`
    ]]
</ul>
```

```php
[[- Chunk Name: instagram-item ]]
[[+type:isnt=`IMAGE`:then=`PANIC!!!!! [[+link]]`:else=``]]
<li data-index="[[+idx]]">
    <a href="[[+link]]">
        <img src="[[+src]]" alt="[[+caption]]">
    </a>
</li>
```
