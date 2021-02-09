<?php

declare(strict_types=1);

namespace InvincibleTechSystems\EaseAmpMysql;

use \InvincibleTechSystems\EaseAmpMysql\Exceptions\EaseAmpMysqlException;

/*
* Name: CustomAmphpDnsConfigLoader
*
* Author: Raghuveer Dendukuri
*
* Company: Invincible Tech Systems
*
* Version: 1.0.0.
*
* Description: This is to define known recursive DNS server IP Addresses, so that DNS resolution of hostnames do happen, irrespective of whether /etc/resolv.conf
* file is accessible or not to amphp/dns package. This class is inspired from the Custom Config anonymous class example, that is provided in amphp/dns package, to * give more flexibility and to ensure dependency packages and/or applications that do use this library, always have access to recursive dns server info. 
*
* License: MIT
*
* @copyright 2020-2021 Invincible Tech Systems
*/

class CustomAmphpDnsConfigLoader implements \Amp\Dns\ConfigLoader {
	
	private $customRecursiveDnsServersList;
	private $timeoutInSeconds;
	private $numberOfRetryAttempts;

	public function __construct(array $customRecursiveDnsServersList, int $timeoutInSeconds = 5000, int $numberOfRetryAttempts = 3) {
		
		$this->customRecursiveDnsServersList = $customRecursiveDnsServersList;
		$this->timeoutInSeconds = $timeoutInSeconds;
		$this->numberOfRetryAttempts = $numberOfRetryAttempts;
		
		$this->loadConfig();
    }
	
	public function loadConfig(): \Amp\Promise
    {
		try {
			
			$customRecursiveDnsServersListValues = $this->customRecursiveDnsServersList;
			$timeoutInSecondsValue = $this->timeoutInSeconds;
			$numberOfRetryAttemptsValue = $this->numberOfRetryAttempts;
			
			return \Amp\call(function () use ($customRecursiveDnsServersListValues, $timeoutInSecondsValue, $numberOfRetryAttemptsValue){
				$hosts = yield (new \Amp\Dns\HostLoader)->loadHosts();

				return new \Amp\Dns\Config($customRecursiveDnsServersListValues, $hosts, $timeoutInSecondsValue, $numberOfRetryAttemptsValue);
			});
			
		} catch (CustomAmphpDnsConfigLoaderException $e) {
			
			echo "\n CustomAmphpDnsConfigLoaderException - ", $e->getMessage(), (int)$e->getCode();
			
		}
		
    }
	
}
?>