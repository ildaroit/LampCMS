<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is lisensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 * 	  the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website's Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attibutes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2011 (or current year) ExamNotes.net inc.
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */


namespace Lampcms;

/**
 * Static class that holds html vsprintf-based
 * templates for generating login form
 * and its components
 */
class LoginForm
{

	/**
	 * Makes the html form
	 * for the login block
	 *
	 * @return string HTML with
	 * the login form
	 */
	public static function makeLoginForm(Registry $oRegistry)
	{

		$oIni = $oRegistry->Ini;
		d('SESSION: '.print_r($_SESSION, 1));

		if( empty($_SESSION['login_form']) || !empty($_SESSION['login_error'])) {
			d('cp ');

			$linkForgot = '/remindpwd/';
			d('cp');

			$linkReg = '/register/';
			$gfcButton = $twitterButton = $fbButton = '';


			$GfcSiteID = $oIni->GFC_ID;
			if(extension_loaded('curl') && !empty($GfcSiteID)){
				d('cp '.strlen($gfcButton));

				$gfcButton =  '<tr><td></td>
								<td colspan="3" align="left" class="gfc_signup">
								<div class="extauth"><img id="gfcsignin" class="ajax gfcsignin" src='.IMAGE_SITE.'"/images/gfcbutton.jpg" width="226" height="40" alt="Sign in with Google Friend Connect"/></div>			
								</td>
								</tr>';

			}

			if(extension_loaded('oauth') && isset($oIni->TWITTER)){
				$aTW = $oIni['TWITTER'];
				if(!empty($aTW['TWITTER_OAUTH_KEY']) && !empty($aTW['TWITTER_OAUTH_SECRET'])){
					d('$aTW: '.print_r($aTW, 1));
					$twitterButton = '<img id="twsignin" class="ajax twsignin" src='.IMAGE_SITE.'"/images/signin.png" width="151" height="24" alt="Sign in with Twitter account">';
				}
			}


			if(extension_loaded('curl') && isset($oIni->FACEBOOK)){
				$aFB = $oIni['FACEBOOK'];

				if(!empty($aFB['APP_ID'])){
					d('$aFB: '.print_r($aFB, 1));
					$fbButton = '<img id="fbsignup" class="ajax fbsignup" src='.IMAGE_SITE.'"/images/fblogin.png" width="154" height="22" alt="Sign in with Facebook account">';
				}
			}

			$error = (!empty($_SESSION['login_error'])) ? $_SESSION['login_error'] : '';
			d('$error: '.$error);

			$aVals = array(
			'Username required',
			'Username',
			'Password',
			'Remember',
			$linkForgot,
			'Forgot password',
			$linkReg,
			'Create account',
			'Log in',
			$gfcButton,
			$twitterButton,
			$fbButton,
			$error );

			d('cp');
			$html = \tplLoginform::parse($aVals, false);
			
			/**
			 * If !empty($_SESSION['login_error']) is set
			 * then don't store the login form in session
			 * and just return it. 
			 * Otherwise the form with the login error 
			 * message will be cached and will be passed from
			 * page to page even if user not trying to login again
			 * 
			 * In this case we unset login error from session
			 * so it will not be reused on next page
			 * and just return login form, so login
			 * form is not caches in session and login error
			 * is only show this one time!
			 */
			if(!empty($_SESSION['login_error'])){
				unset($_SESSION['login_error']);
				
				return $html;
			}

			$_SESSION['login_form'] = $html;
		}

		d('login form: '.$_SESSION['login_form']);

		return $_SESSION['login_form'];
	}


	public static function makeWelcomeMenu(Registry $oRegistry)
	{

		$oViewer = $oRegistry->Viewer;

		/**
		 * @todo Also is isNewRegistration
		 * then also only make a login form and NO WELCOME MENU
		 * because this means that user has not finished the registration yet
		 * But.... then it would mean that if user joined with Twitter
		 * and have not finished registration he cannot login?
		 *
		 * This is probably not possible because when user is joined with
		 * external auth, then his uid is already not 0, so
		 * automatically it will evaluate to false.
		 *
		 * It's not really possible (Not now at least) to have
		 * isNewUser set to true in user object but not have uid
		 *
		 * Used to be false === (0 < $oViewer->getUid())
		 * If not logged it then uid === 0
		 * 0 < 0 is false, means it evaluates to true
		 *
		 * if logged in then 0 < 34 is true,
		 * means it evaluates to false
		 *
		 */
		if(($oViewer->isGuest()) && (!$oViewer->isNewUser())){
			d('going to make login form');

			return self::makeLoginForm($oRegistry);
		}

		if(empty($_SESSION['welcome'])){
			$addNewPost =  '| <a href="/ask/">Ask Question</a>';

			d('cp');

			$a = array(
			$oViewer->getAvatarImgSrc(),
			'Welcome back',
			$oViewer->getDisplayName(),
			'Settings',
			$addNewPost,
			'Sign out',
			self::makeInviteLink($oViewer),
			'/logout/'
			);

			d('cp aVals: '.print_r($a, 1));
			$_SESSION['welcome'] = \tplWelcome::parse($a, false);
			d('SESSION[welcome]: '.$_SESSION['welcome']);
		} else {
			d('Welcome menu already existed! : '.$_SESSION['welcome']);
		}

		return $_SESSION['welcome'];
	}

	/**
	 * Make extra links like 'Invite Friends' based
	 * on what type of user is currently logged in
	 *
	 * @param clsUserObject $oUser
	 *
	 * @return string HTML string for links
	 */
	protected static function makeInviteLink(User $oUser)
	{

		switch(true){
			case ($oUser instanceof UserGfc):
				$ret = '<div class="gfclinks"><a href="#" id="gfcset" class="ajax">FriendConnect settings</a> | <a href="#" class="ajax" id="gfcinvite">Invite friends</a></div>';
				break;
			case ($oUser instanceof UserFacebook):
				$ret = '<div class="gfclinks"><a href="#" class="ajax" id="fbinvite" title="I\'ve registered at this site, and think you will enjoy your stay here too! Please check it out!">Invite your facebook friends</a></div>';
				break;

			case ($oUser instanceof UserTwitter):
				$ret = '<div class="gfclinks"><a href="#" class="ajax" id="twinvite" title="Hey everyone! Join me at this site (Use the *Sign in with Twitter* button) ">Invite your Twitter friends</a></div>';
				break;
			default:
				$ret = '';
		}

		return $ret;
	}

}
