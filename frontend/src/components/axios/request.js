import axios from 'axios';

export function req(options) {
    return new Promise((resolve, reject) => {
        const instance = axios.create({
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            timeout: 1000*5
        });
        instance(options)
            .then(response => {
                resolve(response.data);
            })
            .catch(error => {
                console.log('axios错误：' + error);
                reject({
                    'status': error.response ? error.response.status : '400',
                    'data': error.response ? error.response.data : error.message
                });
            })
    });
}
