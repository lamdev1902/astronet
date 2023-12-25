<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('MCInfo')) :
	class MCInfo {
		public $settings;
		public $config;
		public $plugname = 'malcare';
		public $brandname = 'MalCare';
		public $badgeinfo = 'mcbadge';
		public $ip_header_option = 'mcipheader';
		public $brand_option = 'bv_whitelabel_infos';
		public $version = '5.42';
		public $webpage = 'https://www.malcare.com';
		public $appurl = 'https://app.malcare.com';
		public $slug = 'malcare-security/malcare.php';
		public $plug_redirect = 'mcredirect';
		public $logo = '../img/logo.png';
		public $brand_icon = '/img/icon.svg';
		public $services_option_name = 'mcconfig';
		public $author = 'MalCare Security';
		public $title = 'MalCare WordPress Security Plugin - Malware Scanner, Cleaner, Security Firewall';

		const DB_VERSION = '4';

		public function __construct($settings) {
			$this->settings = $settings;
			$this->config = $this->settings->getOption($this->services_option_name);
		}

		public function getCurrentDBVersion() {
			$bvconfig = $this->config;
			if ($bvconfig && array_key_exists('db_version', $bvconfig)) {
				return $bvconfig['db_version'];
			}
			return false;
		}

		public function hasValidDBVersion() {
			return MCInfo::DB_VERSION === $this->getCurrentDBVersion();
		}

		public function getLatestWooCommerceDBVersion() {
			if (defined('WC_ABSPATH') && file_exists(WC_ABSPATH . 'includes/class-wc-install.php')) {
				include_once WC_ABSPATH . 'includes/class-wc-install.php';

				if (class_exists('WC_Install')) {
					$update_versions = array_keys(WC_Install::get_db_update_callbacks());

					if (!empty($update_versions)) {
						asort($update_versions);
						return end($update_versions);
					}
				}
			}

			return false;
		}

		public static function getRequestID() {
			if (!defined("BV_REQUEST_ID")) {
				define("BV_REQUEST_ID", uniqid(mt_rand()));
			}
			return BV_REQUEST_ID;
		}

		public function canSetCWBranding() {
			if (MCWPSiteInfo::isCWServer()) {

				$bot_protect_accounts = MCAccount::accountsByType($this->settings, 'botprotect');
				if (sizeof($bot_protect_accounts) >= 1)
					return true;

				$bot_protect_accounts = MCAccount::accountsByPattern($this->settings, 'email', '/@cw_user\.com$/');
				if (sizeof($bot_protect_accounts) >= 1)
					return true;
			}

			return false;
		}

		public function canWhiteLabel($slug = NULL) {
			if (array_key_exists("bv_override_global_whitelabel", $_REQUEST)) {
				return false;
			}
			if (array_key_exists("bv_override_plugin_whitelabel", $_REQUEST) && isset($slug) &&
				$_REQUEST["bv_override_plugin_whitelabel"] === $slug) {
				return false;
			}
			return true;
		}

		public function getPluginWhitelabelInfo($slug = null) {
			if ($slug === null) {
				$slug = $this->slug;
			}
			$whitelabel_infos = $this->getPluginsWhitelabelInfos();
			if (!array_key_exists($slug, $whitelabel_infos) || !is_array($whitelabel_infos[$slug])) {
				return array();
			}
			return $whitelabel_infos[$slug];
		}

		public function getBrandInfo() {
			return $this->settings->getOption($this->brand_option);
		}

		public function getPluginsWhitelabelInfos() {
			$whitelabel_infos = $this->settings->getOption($this->brand_option);
			return is_array($whitelabel_infos) ? $whitelabel_infos : array();
		}

		public function getPluginsWhitelabelInfoByTitle() {
			$whitelabel_infos = $this->getPluginsWhitelabelInfos();
			$whitelabel_infos_by_title = array();
			foreach ($whitelabel_infos as $slug => $whitelabel_info) {
				if (is_array($whitelabel_info) && array_key_exists('default_title', $whitelabel_info) && isset($whitelabel_info['default_title'])) {
					$whitelabel_info['slug'] = $slug;
					$whitelabel_infos_by_title[$whitelabel_info['default_title']] = $whitelabel_info;
				}
			}
			return $whitelabel_infos_by_title;
		}

		public function getBrandName() {
			$brand = $this->getPluginWhitelabelInfo();
			if (is_array($brand) && array_key_exists('menuname', $brand)) {
				return $brand['menuname'];
			}
		  $bvinfo = new MCInfo($this->settings);
if ($bvinfo->canSetCWBranding()) {
				return "Cloudways";
			}

			return $this->brandname;
		}

		public function getBrandIcon() {
			$brand = $this->getPluginWhitelabelInfo();
			if (is_array($brand) && array_key_exists('brand_icon', $brand)) {
				return $brand['brand_icon'];
			}
			return $this->brand_icon;
		}

		public function getWatchTime() {
			$time = $this->settings->getOption('bvwatchtime');
			return ($time ? $time : 0);
		}

		public function appUrl() {
			if (defined('BV_APP_URL')) {
				return BV_APP_URL;
			} else {
				$brand = $this->getPluginWhitelabelInfo();
				if (is_array($brand) && array_key_exists('appurl', $brand)) {
					return $brand['appurl'];
				}
				return $this->appurl;
			}
		}

		public function isActivePlugin() {
			$expiry_time = time() - (3 * 24 * 3600);
			return ($this->getWatchTime() > $expiry_time);
		}

		public function isValidEnvironment(){
			$bvsiteinfo = new MCWPSiteInfo();
			$bvconfig = $this->config;

			if (is_multisite()) {
				return true;
			} elseif ($bvconfig && array_key_exists("siteurl_scheme", $bvconfig)) {
				$siteurl = $bvsiteinfo->siteurl('', $bvconfig["siteurl_scheme"]);
				if (array_key_exists("abspath", $bvconfig) &&
						array_key_exists("siteurl", $bvconfig) && !empty($siteurl)) {
					return ($bvconfig["abspath"] == ABSPATH && $bvconfig["siteurl"] == $siteurl);
				}
			}
			return true;
		}

		public function isProtectModuleEnabled() {
			return $this->isServiceActive("protect") && $this->isValidEnvironment();
		}

		public function isDynSyncModuleEnabled() {
			if ($this->isServiceActive("dynsync")) {
				$dynconfig = $this->config['dynsync'];
				if (array_key_exists('dynplug', $dynconfig) && ($dynconfig['dynplug'] === $this->plugname)) {
					return true;
				}
			}
			return false;
		}

		public function isServiceActive($service) {
			$bvconfig = $this->config;
			if ($bvconfig && array_key_exists('services', $bvconfig)) {
				return in_array($service, $bvconfig['services']) && $this->isActivePlugin();
			}
			return false;
		}

		public function isActivateRedirectSet() {
			return ($this->settings->getOption($this->plug_redirect) === 'yes') ? true : false;
		}

		public function isMalcare() {
			return $this->getBrandName() === 'MalCare';
		}

		public function isBlogvault() {
			return $this->getBrandName() === 'BlogVault';
		}

		public function info() {
			return array(
				"bvversion" => $this->version,
				"sha1" => "true",
				"plugname" => $this->plugname
			);
		}
	}
endif;