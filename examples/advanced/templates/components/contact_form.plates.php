<?php namespace ProcessWire;

/**
 * $this->matchTrue() provided by the Conditionals extension
 */
?>
<form class="contact-form" action="./">
  <ul>
    <li>
      <label>
        <span class="label-text required"><?=__('Email Address')?>*</span>
        <?php if ($errors): ?>
          <span class="error-message"><?=$this->matchTrue([
              'Email address is required' => in_array('missing', $errors),
              'Invalid email address' => in_array('invalid', $errors),
            ])?></span>
        <?php endif ?>
        <input type="text" name="email_address" placeholder="you@domain.com">
      </label>
    </li>
    <li>
      <button type="submit"><?=__('Submit')?></button>
    </li>
  </ul>
</form>