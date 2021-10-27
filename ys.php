<?php
class ys
{
    private $apiDomain = 'https://hk4e-api.mihoyo.com';
    private $apiDomain2 = 'https://hk4e-api-os.mihoyo.com';
    private $queryString;

    public function __construct($inputUrl)
    {
        $parsedInput = parse_url($inputUrl);
        $this->queryString = $parsedInput['query'];
    }

    public function getTypeUrl()
    {
        $typeUrl = "{$this->apiDomain}/event/gacha_info/api/getConfigList?{$this->queryString}";//获取$typeMap用
        return $typeUrl;
    }

    public function getTypeMap()
    {
        $typeMap = [
            '100' => 'new',
            '200' => 'common',
            '301' => 'char',
            '302' => 'weapon',
        ];
        return $typeMap;
    }

    private function fetch($url)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, false);
        curl_setopt($c, CURLOPT_FAILONERROR, true);
        curl_setopt($c, CURLOPT_TIMEOUT, 10);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        $result = curl_exec($c);
        curl_close($c);
        return $result;
    }

    private function fetchOnePage($page, $type, $endId = '')
    {
        $query = "{$this->queryString}&gacha_type={$type}&page={$page}&size=20";
        if ($endId) {
            $query .= "&end_id={$endId}";
        }
        $url = "{$this->apiDomain}/event/gacha_info/api/getGachaLog?{$query}";

        $resp = $this->fetch($url);
        $resp = json_decode($resp, true);
        return $resp['data']['list'] ?? [];
    }

    public function fetchAll($type)
    {
        $list = [];
        $page = 1;
        $addList = $this->fetchOnePage($page, $type);
        while ($addList) {
            $list = array_merge($list, $addList);
            $page++;
            $count = count($list);
            $endId = $list[$count - 1]['id'] ?? '';
            $addList = $this->fetchOnePage($page, $type, $endId);
            var_dump($endId);
        }
        return $list;
    }
}

$input = 'https://user.mihoyo.com/?im_out=false&sign_type=2&auth_appid=im_ccs&authkey_ver=1&win_direction=landscape&lang=zh-cn&device_type=mobile&ext=%7b%22loc%22%3a%7b%22x%22%3a2268.75048828125%2c%22y%22%3a216.97637939453126%2c%22z%22%3a-879.493896484375%7d%2c%22platform%22%3a%22IOS%22%7d&game_version=CNRELiOS2.2.0_R4547778_S4700911_D4645171&plat_type=ios&authkey=fbvJQkDt0zP%2bbLOAhq4nd3GOnca12faz3GvTJc5DChdk6npToKEwmk86qn8QvlvGfblDNxrgQkjuLh4mfQADGJ0L0usmfwz9FyqSECpgq5XFVFwxhQqU8in8zBqhRD3TLFBjtLKQ%2flIGChL9vIW7OQ118S82nycI2mLaATbmmcdpDFjzqn4OJXNHFOBDGoRGreOYfu8Ts48l%2bffcHj9lZOFPPFqrfVfPsSHb5MOfLssKcsvGvTIYUyDSS3KKc%2fjITKnNvjstg%2bqTs%2f5es6%2fugHqm0bUpJocHF1nqtWJ0NG5XiSeNgz5MzXHI%2bdfwbCAF0PC4Hrco%2fpxsQJWQ48wBbIWKBCZembb8FyeX8esPoDo%2bpIu7r5uaYqdTo863vlOTlD4CmrZR4R43%2bovxvjnxHJ5orUaJQ%2bQvjkhDaX2O4QsXVWmohE9bXCtMYj%2fENIHYNriAHIJyUAlp%2fxUFYOvJbq%2bJSagP%2bBsnBhvTl1NEHp98lIG2xNfVsuDtq2XgYQLs%2foM4TiMhRZ58R2l2Dn7jKdsFSvd6ykAVJRBd86Zf8Gckhdm7WQjzUo0LSbck21A%2f%2bTutuVUmOaI2Yf2gUgRL7oVqXngh0K9w5XhBq74wGh4e7UFqQNkkLbNkFBlIR5sIv5xk4Ac3EL5NHkTG1sKuCTZWYo0l6TwSvODPo3sSqBFbRQEyKLOyd7hIG17NRVQmFxri4zqQkl%2bfXqFQNYA0289ASFHzieJUQGeeax7i7guDwQcEJE1o7qs0JAB12GQM4SmG0dXQV2GrG67IbVy34GXnHIMC301q5OZHYnRXzVnjOvDbvRtIN3%2bMG07ZNO126otpLhfJUnL%2b4JgeOb1RhYUioxpOfE9uGH0pNi4JNfoutYCxW7FDCxA51osMuK4DQpOBcpVWiCL2Z9I6i%2f4i5dfeVm8%2fGSOwfQTIrRt1RDlA9wqgPR%2bG4Fwl8r5JDDwuaZA9wCx9Ye2ZB%2bWho4Dd7FQ6vEwgbjA9iX2pUekWMgsnAUiN1D%2fqSuiGAvVnpIfN&game_biz=hk4e_cn#/login/captcha';

$ys = new ys($input);

$type = '100';
$result = $ys->fetchAll($type);
$result = json_encode($result);
file_put_contents('./ys_'.$type.'.json', $result);

