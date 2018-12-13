<?php namespace App\Mail;

use App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Common\Mail\MailTemplates;
use Illuminate\Queue\SerializesModels;

class ShareMediaItem extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $emailMessage;

    /**
     * @var string
     */
    private $emails;

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $itemName;

    /**
     * Create a new message instance.
     *
     * @param string $displayName
     * @param string $emailMessage
     * @param string $emails
     * @param string $link
     * @param string $itemName
     */
    public function __construct($displayName, $emailMessage, $emails, $link, $itemName)
    {
        $this->link = $link;
        $this->emails = $emails;
        $this->itemName = $itemName;
        $this->displayName = $displayName;
        $this->emailMessage = $emailMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $template = App::make(MailTemplates::class)->getByAction('share',
            ['display_name' => $this->displayName, 'item_name' => $this->itemName]
        );

        return $this->to($this->emails)
            ->subject($template['subject'])
            ->view($template['html_view'])
            ->text($template['plain_view']);
    }
}
