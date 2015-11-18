<?php namespace Sajjadrad\Invi;

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Sajjad Rad
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN
 * AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package    Invi
 * @version    0.7 l4
 * @author     Sajjad Rad [sajjad.273@gmail.com]
 * @license    MIT License (3-clause)
 * @copyright  (c) 2014
 * @link       http://sajjadrad.com.com
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Carbon\Carbon;

class Invitation extends \Eloquent
{
    protected $table = 'invitations';
    protected $fillable = array('code','email','expiration','active','used', 'account_id');
        
    public static function boot()
    {
        parent::boot();

        static::created(function($invitation)
        {
            $payload = array(
                'invitation' => $invitation,
                'account'    => $invitation->account,
            );
            \Event::fire('invitation.created', array($payload));
        });
    }
    
    /**
     * Returns the current model status without hitting the database again
     * 
     * @return string possible values [deactive|used|expired|valid]
     */
    public function getStatusAttribute() {
        if(!$this->active) {
            return "deactive";
        } elseif ($this->used) {
            return "used";
        } elseif (strtotime("now") > strtotime($this->expiration)) {
            return "expired";
        } else {
            return "valid";
        }
    }
    
    /**
     * Returns a string built with Carbon using current locale since ver 1.18.0
     * 
     * @return string
     */
    public function getExpirationForHumansAttribute() {
        $expiration = Carbon::parse($this->expiration);
        return Carbon::now()->modify($expiration)->diffForHumans();
    }
    
    public function account() { return $this->belongsTo('User'); }
    public function user() { return $this->belongsTo('User', 'email', 'email'); }
}