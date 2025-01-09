<?php namespace ProcessWire;
/**
 * @property string|null $title Title above the form
 */
?>
<div>
  <h2><?=$title ?? __('Sign Up For Our Newsletter')?></h2>
  <form action="/">
    <label for="email"></label>
    <input id="email" type="email" placeholder="you@domain.com">
    <input type="submit" value="<?=__('Sign Up')?>">
  </form>
</div>



<!--
  If the Wire Objects Extension is enabled, the above could be rewritten with a randomized ID to
  render the component more than once on the same page
-->
<?php namespace ProcessWire;
/**
 * @property string|null $title Title above the form
 */

$emailLabelId = $this->wireRandom->alphanumeric();
?>
<div>
  <h2><?=$title ?? __('Sign Up For Our Newsletter')?></h2>
  <form action="/">
    <label for="<?=$emailLabelId?>"></label>
    <input id="<?=$emailLabelId?>" type="email" placeholder="you@domain.com">
    <input type="submit" value="<?=__('Sign Up')?>">
  </form>
</div>
