<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
    <input type="hidden" name="cmd" value="_donations">
    <input type="hidden" name="business" value="{{ $charity->email }}">
    <input type="hidden" name="lc" value="IE">
    <input type="hidden" name="item_name" value="{{ $charity->name }}">
    <input type="hidden" name="no_note" value="0">
    <input type="hidden" name="currency_code" value="EUR">
    <input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_SM.gif:NonHostedGuest">
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
