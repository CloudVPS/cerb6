<h1>Forgot Password</h1>

{if !empty($register_error)}
<div class="error">{$register_error}</div>
{/if}

<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="a" value="doForgot">

<b>E-mail address:</b><br>
<input type="text" name="email" size="64"><br>
<br>

<button type="submit"><img src="{devblocks_url}c=resource&p=usermeet.core&f=images/check.gif{/devblocks_url}" align="top"> Send Confirmation E-mail</button><br>
<br>

<a href="{devblocks_url}c=register&a=forgot2{/devblocks_url}">Already have a confirmation code?</a><br>

</form> 
