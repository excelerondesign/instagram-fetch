# Instagram Fetch

A simple MODX snippet to get images from instagram using the Instagram Basic Display API.

## Options

-   **tpl**: The name of a chunk, @CHUNK, @FILE, or @INLINE to use as a template for each image. (Dependent on [getResources](https://docs.modx.com/current/en/extras/getresources))
-   **max**: The maximum number of photos to return. The default is 8.

## Placeholders

-   **+type**: Type of Instagram media (Image, Video, Carousel) mostly used for debugging in case Facebook changes the API
-   **+link**: Link to the Instagram post
-   **+src**: Link to the image source
-   **+caption**: Caption associated with the Instagram post
-   **+idx**: 1 based index

## Example

```php
[[+type:isnt=`IMAGE`:then=`PANIC!!!!! [[+link]]`:else=``]]
<li data-index="[[+idx]]">
    <a href="[[+link]]">
        <img src="[[+src]]" alt="[[+caption]]">
    </a>
</li>
```
