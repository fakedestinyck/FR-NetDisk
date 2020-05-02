import {req} from './request.js';

export function axios(url,method,data,token,params) {
    let options = {
        url: (process.env.NODE_ENV === 'production' ? 'http://drive.upset.fun:14122/api/' : 'http://frnetdisk.test/api/')+url,
        method: method
    };
    if (data != undefined && Object.keys(data).length > 0) {
        options['data'] = data;
    }
    if (token != undefined && token !== '') {
        options['headers'] = {'Authorization': 'Bearer '+token};
    }
    if (params != undefined && Object.keys(params).length > 0) {
        options['params'] = params;
    }
    
    return req(options);
}