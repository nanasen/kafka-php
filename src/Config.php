<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +---------------------------------------------------------------------------
// | SWAN [ $_SWANBR_SLOGAN_$ ]
// +---------------------------------------------------------------------------
// | Copyright $_SWANBR_COPYRIGHT_$
// +---------------------------------------------------------------------------
// | Version  $_SWANBR_VERSION_$
// +---------------------------------------------------------------------------
// | Licensed ( $_SWANBR_LICENSED_URL_$ )
// +---------------------------------------------------------------------------
// | $_SWANBR_WEB_DOMAIN_$
// +---------------------------------------------------------------------------

namespace Kafka;

/**
+------------------------------------------------------------------------------
* Kafka protocol since Kafka v0.8
+------------------------------------------------------------------------------
*
* @package
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$
+------------------------------------------------------------------------------
*/

abstract class Config
{
    // {{{ consts

    const SECURITY_PROTOCOL_PLAINTEXT      = 'PLAINTEXT';
    const SECURITY_PROTOCOL_SSL            = 'SSL';
    const SECURITY_PROTOCOL_SASL_PLAINTEXT = 'SASL_PLAINTEXT';
    const SECURITY_PROTOCOL_SASL_SSL       = 'SASL_SSL';

    const SASL_MECHANISMS_PLAIN         = 'PLAIN';
    const SASL_MECHANISMS_GSSAPI        = 'GSSAPI';
    const SASL_MECHANISMS_SCRAM_SHA_256 = 'SCRAM_SHA_256';
    const SASL_MECHANISMS_SCRAM_SHA_512 = 'SCRAM_SHA_512';

    private const ALLOW_SECURITY_PROTOCOLS = [
        self::SECURITY_PROTOCOL_PLAINTEXT,
        self::SECURITY_PROTOCOL_SSL,
        self::SECURITY_PROTOCOL_SASL_PLAINTEXT,
        self::SECURITY_PROTOCOL_SASL_SSL
    ];

    private const ALLOW_MECHANISMS = [
        self::SASL_MECHANISMS_PLAIN,
        self::SASL_MECHANISMS_GSSAPI,
        self::SASL_MECHANISMS_SCRAM_SHA_256,
        self::SASL_MECHANISMS_SCRAM_SHA_512
    ];

    // }}}
    // {{{ members

    protected static $options = [];

    private static $defaults = [
        'clientId'           => 'kafka-php',
        'brokerVersion'      => '0.10.1.0',
        'metadataBrokerList' => '',
        'messageMaxBytes'    => '1000000',
        'metadataRequestTimeoutMs'  => '60000',
        'metadataRefreshIntervalMs' => '300000',
        'metadataMaxAgeMs' => -1,
        'securityProtocol' => self::SECURITY_PROTOCOL_PLAINTEXT,
        'sslEnable'     => false, // this config item will override, don't config it.
        'sslLocalCert'  => '',
        'sslLocalPk'    => '',
        'sslVerifyPeer' => false,
        'sslPassphrase' => '',
        'sslCafile'     => '',
        'sslPeerName'   => '',
        'saslMechanism' => self::SASL_MECHANISMS_PLAIN,
        'saslUsername'  => '',
        'saslPassword'  => '',
        'saslKeytab'    => '',
        'saslPrincipal' => '',
    ];

    // }}}
    // {{{ functions
    // {{{ public function __call()

    public function __call($name, $args)
    {
        if (strpos($name, 'get') === 0 || strpos($name, 'iet') === 0) {
            $option = strtolower(substr($name, 3, 1)) . substr($name, 4);
            if (isset(self::$options[$option])) {
                return self::$options[$option];
            }

            if (isset(self::$defaults[$option])) {
                return self::$defaults[$option];
            }
            if (isset(static::$defaults[$option])) {
                return static::$defaults[$option];
            }
            return false;
        }

        if (strpos($name, 'set') === 0) {
            if (count($args) != 1) {
                return false;
            }
            $option                   = strtolower(substr($name, 3, 1)) . substr($name, 4);
            static::$options[$option] = $args[0];
            // check todo
            return true;
        }
    }

    // }}}
    // {{{ public function setClientId()

    public function setClientId($val)
    {
        $client = trim($val);
        if ($client == '') {
            throw new \Kafka\Exception\Config('Set clientId value is invalid, must is not empty string.');
        }
        static::$options['clientId'] = $client;
    }

    // }}}
    // {{{ public function setBrokerVersion()

    public function setBrokerVersion($version)
    {
        $version = trim($version);
        if ($version == '' || version_compare($version, '0.8.0') < 0) {
            throw new \Kafka\Exception\Config('Set broker version value is invalid, must is not empty string and gt 0.8.0.');
        }
        static::$options['brokerVersion'] = $version;
    }

    // }}}
    // {{{ public function setMetadataBrokerList()

    public function setMetadataBrokerList($list)
    {
        if (trim($list) == '') {
            throw new \Kafka\Exception\Config('Set broker list value is invalid, must is not empty string');
        }
        $tmp   = explode(',', trim($list));
        $lists = [];
        foreach ($tmp as $key => $val) {
            if (trim($val) != '') {
                $lists[] = $val;
            }
        }
        if (empty($lists)) {
            throw new \Kafka\Exception\Config('Set broker list value is invalid, must is not empty string');
        }
        foreach ($lists as $val) {
            $hostinfo = explode(':', $val);
            foreach ($hostinfo as $key => $val) {
                if (trim($val) == '') {
                    unset($hostinfo[$key]);
                }
            }
            if (count($hostinfo) != 2) {
                throw new \Kafka\Exception\Config('Set broker list value is invalid, must is not empty string');
            }
        }

        static::$options['metadataBrokerList'] = $list;
    }

    // }}}
    // {{{ public function clear()

    public function clear()
    {
        static::$options = [];
    }

    // }}}
    // {{{ public function setMessageMaxBytes()

    public function setMessageMaxBytes($messageMaxBytes)
    {
        if (! is_numeric($messageMaxBytes) || $messageMaxBytes < 1000 || $messageMaxBytes > 1000000000) {
            throw new \Kafka\Exception\Config('Set message max bytes value is invalid, must set it 1000 .. 1000000000');
        }
        static::$options['messageMaxBytes'] = $messageMaxBytes;
    }

    // }}}
    // {{{ public function setMetadataRequestTimeoutMs()

    public function setMetadataRequestTimeoutMs($metadataRequestTimeoutMs)
    {
        if (! is_numeric($metadataRequestTimeoutMs) || $metadataRequestTimeoutMs < 10
            || $metadataRequestTimeoutMs > 900000) {
            throw new \Kafka\Exception\Config('Set metadata request timeout value is invalid, must set it 10 .. 900000');
        }
        static::$options['metadataRequestTimeoutMs'] = $metadataRequestTimeoutMs;
    }

    // }}}
    // {{{ public function setMetadataRefreshIntervalMs()

    public function setMetadataRefreshIntervalMs($metadataRefreshIntervalMs)
    {
        if (! is_numeric($metadataRefreshIntervalMs) || $metadataRefreshIntervalMs < 10
            || $metadataRefreshIntervalMs > 3600000) {
            throw new \Kafka\Exception\Config('Set metadata refresh interval value is invalid, must set it 10 .. 3600000');
        }
        static::$options['metadataRefreshIntervalMs'] = $metadataRefreshIntervalMs;
    }

    // }}}
    // {{{ public function setMetadataMaxAgeMs()

    public function setMetadataMaxAgeMs($metadataMaxAgeMs)
    {
        if (! is_numeric($metadataMaxAgeMs) || $metadataMaxAgeMs < 1
            || $metadataMaxAgeMs > 86400000) {
            throw new \Kafka\Exception\Config('Set metadata max age value is invalid, must set it 1 .. 86400000');
        }
        static::$options['metadataMaxAgeMs'] = $metadataMaxAgeMs;
    }

    // }}}
    // {{{ public function setSslLocalCert()

    public function setSslLocalCert($localCert)
    {
        if (! is_string($localCert) || ! file_exists($localCert)) {
            throw new \Kafka\Exception\Config('Set ssl local cert file is invalid');
        }
        static::$options['sslLocalCert'] = $localCert;
    }

    // }}}
    // {{{ public function setSslLocalPk()

    public function setSslLocalPk($localPk)
    {
        if (! is_string($localPk) || ! file_exists($localPk)) {
            throw new \Kafka\Exception\Config('Set ssl local private key file is invalid');
        }
        static::$options['sslLocalPk'] = $localPk;
    }

    // }}}
    // {{{ public function setSslCafile()

    public function setSslCafile($cafile)
    {
        if (! is_string($cafile) || ! file_exists($cafile)) {
            throw new \Kafka\Exception\Config('Set ssl ca file is invalid');
        }
        static::$options['sslCafile'] = $cafile;
    }

    // }}}
    // {{{ public function setKeytab()

    public function setKeytab($keytab)
    {
        if (! is_string($keytab) || ! file_exists($keytab)) {
            throw new \Kafka\Exception\Config('Set ssl ca file is invalid');
        }
        static::$options['saslKeytab'] = $keytab;
    }

    // }}}
    // {{{ public function setSecurityProtocol()

    public function setSecurityProtocol($protocol)
    {
        if (! in_array($protocol, self::ALLOW_SECURITY_PROTOCOLS, true)) {
            throw new \Kafka\Exception\Config('Invalid security protocol given.');
        }

        static::$options['securityProtocol'] = $protocol;
    }

    // }}}
    // {{{ public function setSaslMechanism()

    public function setSaslMechanism($mechanism)
    {
        if (! in_array($mechanism, self::ALLOW_MECHANISMS, true)) {
            throw new \Kafka\Exception\Config('Invalid security sasl mechanism given.');
        }

        static::$options['saslMechanism'] = $mechanism;
    }

    // }}}
    // }}}
}
