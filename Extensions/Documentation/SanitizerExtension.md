### Sanitizer Extension

This extension is a wrapper for all ProcessWire sanitizer functions. Any method that you can call on the `$sanitizer` object, you can call using this extension. This provides the benefit of allowing any ProcessWire sanitizer functions that accept a single value in `batch()` chains.

To call any ProcessWire sanitizer, prefix the function name with `sanitizer` as camel case. Example: `url()` is called as `sanitizeUrl()`.

For a full list of supported ProcessWire sanitizer functions, review `ProcessWire\Sanitizer`, and the ProcessWire sanitizer documentation.

```php
<!-- Outputs 'abcdef' -->
<p>Just letters: <?=$this->sanitizeAlpha('abc123def')?></p>

<!-- Batch with native PHP or other extension functions, outputs 'hello@test.com' -->
<p>Your email is: $this->batch('hElLO@TesT.CoM', 'strtolower|sanitizeEmail')</p>
```
