<h2><?= __( 'How to get your OpenAI API key ?', $this->prefix ); ?></h2>
<ol>
    <li>
        <?= __( 'Sign up / Log in into OpenAI developer portail', $this->prefix ); ?> : 
        <a target="_blank" title="OpenAI Developer Portail" href="https://openai.com/api/">https://openai.com/api/</a>
    </li>
    <li>
        <?= __( 'In User > View API keys, create a new secret key', $this->prefix ); ?> :
        <a target="_blank" title="OpenAI - API keys" href="https://platform.openai.com/account/api-keys">https://platform.openai.com/account/api-keys</a>
    </li>
    <li>
        <?= __( 'Copy and paste the new secret key in the OPENAI_API_KEY field right here.', $this->prefix ); ?> 
    </li>
    <li>
        <?= __( 'Press "Save changes" and you are done.', $this->prefix ); ?> 
    </li>
</ol>
<?php settings_errors(); ?>
<form method="post" action="options.php">
    <?php
        settings_fields( $this->prefix.'_option_group' );
        do_settings_sections( $this->prefix.'-admin' );
        submit_button();
    ?>
</form>