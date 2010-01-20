<?php

/**
 * This file supplies a dumb store backend for OpenID servers and
 * consumers.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Import the interface for creating a new store class.
 */
require_once 'Auth/OpenID/Interface.php';
require_once 'Auth/OpenID/HMAC.php';

/**
 * This is a store for use in the worst case, when you have no way of
 * saving state on the consumer site. Using this store makes the
 * consumer vulnerable to replay attacks, as it's unable to use
 * nonces. Avoid using this store if it is at all possible.
 *
 * Most of the methods of this class are implementation details.
 * Users of this class need to worry only about the constructor.
 *
 * @package OpenID
 */
class Auth_OpenID_DumbStore extends Auth_OpenID_OpenIDStore {

    /**
     * Creates a new {@link Auth_OpenID_DumbStore} instance. For the security
     * of the tokens generated by the library, this class attempts to
     * at least have a secure implementation of getAuthKey.
     *
     * When you create an instance of this class, pass in a secret
     * phrase. The phrase is hashed with sha1 to make it the correct
     * length and form for an auth key. That allows you to use a long
     * string as the secret phrase, which means you can make it very
     * difficult to guess.
     *
     * Each {@link Auth_OpenID_DumbStore} instance that is created for use by
     * your consumer site needs to use the same $secret_phrase.
     *
     * @param string secret_phrase The phrase used to create the auth
     * key returned by getAuthKey
     */
    function Auth_OpenID_DumbStore($secret_phrase)
    {
        $this->auth_key = Auth_OpenID_SHA1($secret_phrase);
    }

    /**
     * This implementation does nothing.
     */
    function storeAssociation($server_url, $association)
    {
    }

    /**
     * This implementation always returns null.
     */
    function getAssociation($server_url, $handle = null)
    {
        return null;
    }

    /**
     * This implementation always returns false.
     */
    function removeAssociation($server_url, $handle)
    {
        return false;
    }

    /**
     * In a system truly limited to dumb mode, nonces must all be
     * accepted. This therefore always returns true, which makes
     * replay attacks feasible.
     */
    function useNonce($server_url, $timestamp, $salt)
    {
        return true;
    }

    /**
     * This method returns the auth key generated by the constructor.
     */
    function getAuthKey()
    {
        return $this->auth_key;
    }
}

?>
