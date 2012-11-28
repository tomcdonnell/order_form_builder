<?php
/*
 * vim: ts=4 sw=4 et wrap co=100 go-=b
 */

define('EMAIL_ADDRESSES_FILENAME', 'email_addresses_one_per_line.txt');
define('EMAIL_TEXT_FILENAME'     , 'email_text.txt');

try
{
    $boolActuallySendEmails = validateArgvAndReturnWhetherActualSuppliedOrOutputUsageAndDie();
    $emailText              = @file_get_contents(EMAIL_TEXT_FILENAME     );
    $emailAddressesString   = @file_get_contents(EMAIL_ADDRESSES_FILENAME);

    if ($emailText === false)
    {
        echo "Error reading file '" . EMAIL_TEXT_FILENAME . "'.  Does the file exist?\n";
        die();
    }

    if ($emailText === false)
    {
        echo "Error reading file '" . EMAIL_ADDRESSES_FILENAME . "'.  Does the file exist?\n";
        die();
    }

    // TODO: Get subject from first line of email text.
    $emailSubject   = 'Performance Management Online - Reminder';
    $emailAddresses = explode("\n", $emailAddressesString);
    array_pop($emailAddresses);  // The final element will always be blank string.  Discard it.

    if (count($emailAddresses) == 0)
    {
        echo "File '" . EMAIL_ADDRESSES_FILENAME . "' is empty.  Nothing to do.\n";
        die();
    }

    echo "\n";

    $headers = 'From: human.resources@dpi.vic.gov.au';

    foreach ($emailAddresses as $emailAddress)
    {
        switch ($boolActuallySendEmails)
        {
          case true:
            mail($emailAddress, $emailSubject, $emailText, $headers);
            $str = 'Sent';
            break;
          case false:
            $str = 'If --actual was used, would have sent';
            break;
        }

        echo "$str email to $emailAddress.\n";
    }
}
catch (Exception $e)
{
    echo $e->getMessage();
}

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function validateArgvAndReturnWhetherActualSuppliedOrOutputUsageAndDie()
{
    global $argv;
  
    $usageString =
    (
        "\n" . str_repeat('-', 79) . "\n"                                                .
        "\n\"send_bulk_emails.php\"\n\n"                                                 .
        "Description\n"                                                                  .
        "-----------\n"                                                                  .
        "Send the contents of file " . EMAIL_TEXT_FILENAME . " as an email to all email" .
        " addresses in\nfile " . EMAIL_ADDRESSES_FILENAME . ".\n\n"                      .
        "Usage\n"                                                                        .
        "-----\n"                                                                        .
        "php send_bulk_emails.php [argument]\n"                                          .
        "\n"                                                                             .
        "[argument] is one of the following (including the hyphens):\n"                  .
        "   --hypothetical\n"                                                            .
        "     Do nothing, but describe what would have happened if --actual was used.\n" .
        "   --actual\n"                                                                  .
        "     Actually send the emails.\n\n"                                             .
        "\n" . str_repeat('-', 79) . "\n\n"
    );
  
    switch (count($argv))
    {
      case 1 : echo $usageString; die();
      case 2 : break;
      default: echo "\nIncorrect number of arguments.\n$usageString"; die();
    }
  
    switch ($argv[1])
    {
      case '--hypothetical': return false;
      case '--actual'      : return true ;
      default              : echo "\nIncorrect argument '{$argv[1]}'.\n$usageString"; die();
    }
}
?>
