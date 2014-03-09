<?php namespace Cms\App\Validators;

use Illuminate\Support\MessageBag;

use \Charity;
use \SocialLink;

/**
 * A class to validate multiple SocialLinks.
 * @author Aidan Grabe
 */
class SocialLinkValidator {
    
    /**
     * @var Charity
     *      The charity the social links belong to
     */
    private $charity;

    /**
     * @var array
     *      an array of arrays containing the SocialLink columns to be validated
     */
    private $data;

    /**
     * @var MessageBag
     *      A MessageBag containing this validators errors
     */
    private $errors;

    /**
     * @var array
     *      An array of SocialLinks, that will 
     */
    private $socialLinks;

    /**
     * @param Charity $charity the Charity that the SocialLinks belong to
     * @param array $data an array of arrays containing the SocialLink columns
     *      and their values. This is the data that will be validated
     */
    public function __construct(Charity $charity, array $data) {
        $this->charity = $charity;
        $this->data = $data;
        $this->socialLinks = array();
    }

    /**
     * The errors generated by validating the SocialLinks data
     * @return MessageBag 
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * get the array of SocialLinks after being validated
     * @return array
     */
    public function getSocialLinks() {
        return $this->socialLinks;
    }

    /**
     * Validate the data
     * Errors on a single SocialLink will be accumulated into the $errors
     * variable
     * @return boolean true if validation passes, else false
     */
    public function passes() {
        $services = SocialLink::getValidServices();
        $passes = true;

        // get the current links if any
        $socialLinks = $charity->socialLinks;

        $this->errors = new MessageBag();
        foreach ($services as $service) {
            if (!array_key_exists($service, $this->data)) continue;
            
            $socialLink = SocialLink::make($this->charity, $service, $this->data[$service]);

            // check the current links, to see if we are editting one
            foreach ($socialLinks as $link) {
                if ($link->service == $service) {
                    $socialLink = $link;
                    $socialLink->validate(array(
                        'service'   => $service,
                        'charity_id' => $this->charity->charity_id,
                        'url' => $this->data[$service],
                    ));
                    break;
                } // if
            } // foreach

            // logical AND the results together
            $passes &= $socialLink->isValid();
            $this->socialLinks[] = $socialLink;

            // merge the errors
            $this->errors->merge($socialLink->getValidator()->errors()->getMessages());
        } // foreach

        return $passes;
    }

}
