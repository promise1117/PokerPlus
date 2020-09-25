<?php

namespace User\Controller;

class VgController extends \Home\Controller\BaseController
{
    private static $merchantNo;

    public function __construct()
    {
        //商户号
        self::$merchantNo = "2009101727201000001";
    }

    /**
     * User: Tom
     * Date: 2020/9/23 18:05
     * Description: Vg注册接口
     */
    public function register($loginNo='',$userName='',$nickName='',$countryCode='86',$password='')
    {
        /**
        字段名称        字段类型      字段用途        长度    必填  备注
        merchantNo     varchar      商户号         32      是   商户入驻vvv钱包时获得的商户号
        loginNo        varchar      用户登录号       32     是    用户登录游戏的账号（手机号码/邮箱）
        requestDate    varchar      请求时间        32      是   时间格式：yyyyMMddHHmmss
        userName       varchar      用户姓名        64      否
        nickName       varchar      用户昵称        32      是
        countryCode    varchar      用户国籍代码     32      是    86、001、...
        registerIp     varchar      注册IP          16      是
        registerDevice varchar      注册设备        16      是   Android，Ios
        recommendCode  varchar      推荐码         64      是
        recommendUid   varchar      推荐人UID      32      否
        leagueId       number       联盟ID        11      否
        clubId         number       俱乐部ID       11      否
        userType       number       用户类型        16      是   用户类型：1-普通玩家；2-俱乐部；3-联盟
        password       varchar      密码          64      是   用户在游戏端的登录密码
        */
        $merchantNo=self::$merchantNo;
        $loginNo=trim(strval($loginNo));
        $requestDate=date('YmdHis',time());

        $userName=trim(strval($userName));
        $nickName=trim(strval($nickName));
        $countryCode=trim(strval($countryCode));
        $registerIp=$_SERVER['REMOTE_ADDR'];

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $registerDevice="Ios";
        }else{
            $registerDevice="Android";
        }

        $recommendCode="222222";
        $recommendUid='';
        $leagueId='';
        $clubId='';
        $userType=1;
        $password=trim(strval($password));

        $str ="merchantNo=$merchantNo\&loginNo=$loginNo\&countryCode=$countryCode\&registerIp=$registerIp\&registerDevice=$registerDevice\&requestDate=$requestDate\&nickName=$nickName\&recommendCode=$recommendCode\&userType=$userType\&password=$password";

        $sign = $output = shell_exec('./base64_demo '.$str);

        $param = array(
            'merchantNo'=>$merchantNo,
            'loginNo'=>$loginNo,
            'requestDate'=>$requestDate,
            'userName'=>$userName,
            'nickName'=>$nickName,
            'countryCode'=>$countryCode,
            'registerIp'=>$registerIp,
            'registerDevice'=>$registerDevice,
            'recommendCode'=>$recommendCode,
            'recommendUid'=>$recommendUid,
            'leagueId'=>$leagueId,
            'clubId'=>$clubId,
            'userType'=>$userType,
            'password'=>$password,
            'sign'=>$sign
        );
        $url = 'https://www.vg0.top/gateway/api/v1/merchant/register';

        $header = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data = vg_request_post($url,$param,$header);

        return $data;


    }

    /**
     * User: Tom
     * Date: 2020/9/23 18:05
     * Description:Vg登录接口
     */
    public function login($uid = "")
    {

        /**
         * 字段名称	字段类型	字段用途	        长度	    必填	    备注
        merchantNo	varchar	商户号	        32	    是	    商户入驻vvv钱包时获得的商户号
        uid	        varchar	钱包用户唯一标识	32	    是	    钱包用户唯一标识
        loginIp	    varchar	登录IP	        32	    是
        requestDate	varchar	请求时间	        14	    是	    格式：yyyyMMddHHmmss
        sign	    varchar	请求签名串	    256	    是	    商户与vvv钱包请求签名串（签名规则如下）
         */
        $merchantNo=self::$merchantNo;
        $uid=trim(strval($uid));
        $loginIp=$_SERVER['REMOTE_ADDR'];
        $requestDate=date('YmdHis',time());

        $str="merchantNo=".$merchantNo."\&uid=".$uid."\&requestDate=".$requestDate;


        $sign = $output = shell_exec('./base64_demo '.$str);

        $param = array(
            "merchantNo"=> $merchantNo,
            "uid"=> $uid,
            "loginIp"=> $loginIp,
            "requestDate"=> $requestDate,
            'sign'=>$sign
        );
        $url = 'https://www.vg0.top/gateway/api/v1/account/login';

        $header = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data = vg_request_post($url,$param,$header);
        
        exit($data);
    }

    /**
     * @param string $uid
     * @return mixed
     * User: Tom
     * Date: 2020/9/24 16:32
     * Description:通过uid获取用户信息
     */
    public function getUserInfo($uid = "")
    {
        /**
        字段名称        字段类型          字段用途    长度  必填  备注
        merchantNo      varchar         商户号       32 是 商户入驻vvv钱包时获得的商户号
        uid             varchar         用户登录号   32 是 用户的手机号/邮箱
        requestDate     varchar         请求时间     32 是 时间格式：yyyyMMddHHmmss
        sign            varchar         请求签名串   256 是 商户与vvv钱包请求签名串（签名规则如下）
         */

        $merchantNo=self::$merchantNo;
        $uid = trim(strval($uid));
        $requestDate = date('YmdHis',time());


        $str ="merchantNo=$merchantNo\&uid=$uid\&requestDate=$requestDate";

        $sign = $output = shell_exec('./base64_demo '.$str);

        $param = array(
            'merchantNo'=>$merchantNo,
            'uid'=>$uid,
            'requestDate'=>$requestDate,
            'sign'=>$sign
        );
        $url = 'https://www.vg0.top/gateway/api/v1/account/findUser';

        $header = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data = vg_request_post($url,$param,$header);

        return $data;

    }


    /**
     * @param string $uid
     * @param string $clubName
     * @return mixed 俱乐部ID
     * User: Tom
     * Date: 2020/9/24 16:59
     * Description:添加俱乐部
     */
    public function clubAdd($uid = "",$clubName = "")
    {
        /**
        字段名称        字段类型          字段用途    长度  必填  备注
        merchantNo varchar 商户号 32 是 商户入驻vvv钱包时获得的商户号
        uid varchar 钱包用户唯一标识 32 是 钱包用户唯一标识
        clubName varchar 俱乐部名称 64 是 俱乐部名称
        sign varchar 请求签名串 256 是 商户与vvv钱包请求签名串（签名规则如下）
         */

        $merchantNo=self::$merchantNo;
        $uid = trim(strval($uid));
        $clubName = trim(strval($clubName));


        $str ="merchantNo=$merchantNo\&uid=$uid\&clubName=$clubName";

        $sign = $output = shell_exec('./base64_demo '.$str);

        $param = array(
            'merchantNo'=>$merchantNo,
            'uid'=>$uid,
            'clubName'=>$clubName,
            'sign'=>$sign
        );
        $url = 'https://www.vg0.top/gateway/api/v1/merchant/addClub';

        $header = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data = vg_request_post($url,$param,$header);

        return $data;

    }

    /**
     * @param string $uid
     * @param string $leagueName
     * @return mixed 成功返回联盟ID
     * User: Tom
     * Date: 2020/9/24 17:51
     * Description: 添加联盟
     */
    public function allianceAdd($uid = "",$leagueName = "")
    {
        /**
        字段名称        字段类型          字段用途    长度  必填  备注
         * merchantNo varchar 商户号 32 是 商户入驻vvv钱包时获得的商户号
         * uid varchar 钱包用户唯一标识 32 是 钱包用户唯一标识
         * leagueName varchar 联盟名称 64 是 联盟名称
         * sign varchar 请求签名串 256 是 商户与vvv钱包请求签名串（签名规则如下）
         */

        $merchantNo=self::$merchantNo;
        $uid = trim(strval($uid));
        $leagueName = trim(strval($leagueName));


        $str ="merchantNo=$merchantNo\&uid=$uid\&clubName=$leagueName";

        $sign = $output = shell_exec('./base64_demo '.$str);

        $param = array(
            'merchantNo'=>$merchantNo,
            'uid'=>$uid,
            'clubName'=>$leagueName,
            'sign'=>$sign
        );
        $url = 'https://www.vg0.top/gateway/api/v1/merchant/addLeague';

        $header = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data = vg_request_post($url,$param,$header);

        return $data;

    }


    /**
     * @param string $clubId
     * @return mixed
     * User: Tom
     * Date: 2020/9/24 17:55
     * Description: 统计成功返回result统计数量
     */
    public function totalClub($clubId = "")
    {

        $merchantNo=self::$merchantNo;
        $clubId = trim(strval($clubId));
        $requestDate = date('YmdHis',time());


        $str ="merchantNo=$merchantNo\&requestDate=$requestDate\&clubId=$clubId";

        $sign = $output = shell_exec('./base64_demo '.$str);

        $param = array(
            'merchantNo'=>$merchantNo,
            'requestDate'=>$requestDate,
            'clubId'=>$clubId,
            'sign'=>$sign
        );
        $url = 'https://www.vg0.top/gateway/api/v1/merchant/totalClub';

        $header = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data = vg_request_post($url,$param,$header);

        return $data;

    }


    /**
     * @param string $leagueId
     * @return mixed
     * User: Tom
     * Date: 2020/9/24 17:57
     * Description: 统计联盟成功返回result统计数量
     */
    public function totalLeague($leagueId = "")
    {

        $merchantNo=self::$merchantNo;
        $leagueId = trim(strval($leagueId));
        $requestDate = date('YmdHis',time());


        $str ="merchantNo=$merchantNo\&requestDate=$requestDate\&leagueId=$leagueId";

        $sign = $output = shell_exec('./base64_demo '.$str);

        $param = array(
            'merchantNo'=>$merchantNo,
            'requestDate'=>$requestDate,
            'leagueId'=>$leagueId,
            'sign'=>$sign
        );
        $url = 'https://www.vg0.top/gateway/api/v1/merchant/totalClub';

        $header = array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $data = vg_request_post($url,$param,$header);

        return $data;

    }

}