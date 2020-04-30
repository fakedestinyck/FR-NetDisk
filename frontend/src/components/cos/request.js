var COS = require('cos-js-sdk-v5');
import {
    axios
} from '@/components/axios/api.js';

export function cos(token) {
    return new COS({
        getAuthorization: function (options, callback) {
            console.log('调取签名')
            axios('auth/getKey', 'GET', {}, token)
                .then(response => {
                    let data = response.data
                    var credentials = data && data.credentials
                    if (!data || !credentials) {
                        return console.error('credentials invalid')
                    }
                    callback({
                        TmpSecretId: credentials.tmpSecretId,
                        TmpSecretKey: credentials.tmpSecretKey,
                        XCosSecurityToken: credentials.sessionToken,
                        // 建议返回服务器时间作为签名的开始时间，避免用户浏览器本地时间偏差过大导致签名错误
                        StartTime: data.startTime, // 时间戳，单位秒，如：1580000000
                        ExpiredTime: data.expiredTime, // 时间戳，单位秒，如：1580000900
                    })
                })
                .catch(error => {
                    console.log(error)
                })
                .then(() => {});
        }
    })
}
