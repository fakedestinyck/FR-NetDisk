import {req} from './request.js';

export function axios(url,method,params,token) {
    let options = {
        url: (process.env.NODE_ENV === 'production' ? '' : 'http://frnetdisk.test/api/')+url,
        method: method,
        data: params
    };
    if (token != undefined && token !== '') {
        options['headers'] = {'Authorization': 'Bearer '+token};
    }
    
    return req(options);
}