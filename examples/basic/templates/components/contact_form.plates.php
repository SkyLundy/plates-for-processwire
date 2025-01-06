<?php namespace ProcessWire;

$errors ??= [];
?>
 <form class="contact-form" action="./">
  <ul>
    <li>
      <label>
        <span class="label-text required"><?=__('Email Address')?>*</span>
        <?php if (in_array('missing', $errors)): ?>
          <span class="error-message"><?=__('Email address is required')?></span>
        <?php elseif (in_array('invalid', $errors)): ?>
          <span class="error-message"><?=__('Invalid email address')?></span>
        <?php endif ?>
        <input type="text" name="email_address" placeholder="you@domain.com">
      </label>
    </li>
    <li>
      <button type="submit"><?=__('Submit')?></button>
    </li>
  </ul>
 </form>