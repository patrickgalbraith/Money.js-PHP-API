<?php

/*
 * USAGE
 * 
 * $fx = new Fx();
 * echo $fx->convert(1000)->from('USD')->to('GDP');
 * 
 * $fx->settings(array('from' => 'GDP', 'to' => 'AUD'));
 * echo $fx->convert(1000)->done();
 */

/**
 * Currency coversion library based on money.js v0.1.3 by Joss Crowcroft (http://josscrowcroft.github.com/money.js)
 *
 * @author Patrick Galbraith (http://www.pjgalbraith.com)
 * @license Public Domain
 */
class Fx {
    
    protected $rates_url = 'https://raw.github.com/currencybot/open-exchange-rates/master/latest.json';
    
    private $settings;
    
    private $_ci;
    
    private $from;
    private $to;
    private $base;
    
    private $val;
    private $result;
    
    function __construct($from = 'USD', $to = 'GDP') {
        $this->_ci =& get_instance();
        
        $this->loadRates();
        
        $this->from = $from;
        $this->to = $to;
    }
    
    public function convert($val, $options = array()) {
        $this->val = $val;
        
        if(isset($options['from']))
            $this->from = $options['from'];
        
        if(isset($options['to']))
            $this->to = $options['to'];
        
        return $this;
    }
    
    public function from($from) {    
        
        if(!isset($this->settings->rates->{$from}))
            throw new Exception('Unknown or unsupported currency.');
        
        $this->from = $from;
        return $this;
    }
    
    public function to($to) {
        
        if(!isset($this->settings->rates->{$to}))
            throw new Exception('Unknown or unsupported currency.');
        
        $this->to = $to;
        return $this;
    }
    
    public function settings($options = array()) {
        
        if(isset($options['from']))
            $this->from = $options['from'];
        
        if(isset($options['to']))
            $this->to = $options['to'];
        
        return $this;
    }
    
    public function done() {
        $this->calculate();
        return $this->result;
    }
    
    public function __toString() {
        $this->calculate();
        return (string)$this->result;
    }
    
    protected function calculate() {
        $this->result = $this->val * $this->getRate($this->to, $this->from); //$this->settings->rates->{$this->to};
    }
    
    protected function getRate($to, $from) {
        
		$rates = $this->settings->rates;

		// Make sure the base rate is in the rates object:
		$rates->{$this->settings->base} = 1;

		// Throw an error if either rate isn't in the rates array
		if ( !isset($rates->{$to}) || !isset($rates->{$from}) ) 
            throw new Exception('Unknown or unsupported currency.');

		// If `from` currency === base, return the basic exchange rate for the `to` currency
		if ( $from === $this->settings->base )
			return $rates->{$to};

		// If `to` currency === base, return the basic inverse rate of the `from` currency
		if ( $to === $this->settings->base )
			return 1 / $rates->{$from};

		// Otherwise, return the `to` rate multipled by the inverse of the `from` rate to get the
		// relative exchange rate between the two currencies
		return $rates->{$to} * (1 / $rates->{$from});
    }
    
    protected function loadRates() {
        
        $json = null;
        
        if(($json = $this->_ci->cache->get('fx_json_rates')) === false) {
            
            // Open CURL session
            $ch = curl_init($this->rates_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Get the data
            $json = curl_exec($ch);
            curl_close($ch);

            $this->_ci->cache->save('fx_json_rates', $json, 86400); //cache for one day
        }
        
        // Decode JSON response
        $this->settings = json_decode($json);
        
        $this->base = $this->settings->base;
    }
    
}