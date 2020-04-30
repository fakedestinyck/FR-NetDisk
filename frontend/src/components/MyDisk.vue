<template>
    <div class="mydisk">
        <div class="breadcrumb">
            <at-breadcrumb>
                <at-breadcrumb-item :href="parentBreadcrumb.length <= 1 ? '' : 'javascript:void(0)'" @click.native="goOutsideFolder(-1)">Home</at-breadcrumb-item>
                <template v-if="parentBreadcrumb && parentBreadcrumb !== ''">
                    <at-breadcrumb-item
                        v-for="(path, index) in parentArr"
                        :href="parentArr.length - 1 === index ? '' : 'javascript:void(0)'"
                        @click.native="goOutsideFolder(index)"
                    >
                        {{ path }}
                    </at-breadcrumb-item>
                    <!-- <at-breadcrumb-item :to="{ name: 'Color-zh' }" replace>Color</at-breadcrumb-item> -->
                </template>
            </at-breadcrumb>
        </div>
        <input type="file" v-show="false" ref="fileInput" @change="handleUploadFile" multiple />
        <!-- <at-table :columns="columns1" :data="data2" optional></at-table> -->
        <at-table :columns="tableColumns" :data="myfiles" :height="innerHeight" optional></at-table>
        <div class="add-button-wrapper">
            <transition name="slide-fade">
                <div class="btn-add-popup-wrapper" v-show="showPopupAddButtons">
                    <at-button type="primary" icon="icon-folder" circle class="btn-add btn-add-popup" @click="handleAddFolder"></at-button>
                    <at-button type="primary" icon="icon-file-plus" circle class="btn-add btn-add-popup" @click="chooseFileToUpload"></at-button>
                </div>
            </transition>
            <at-button
                type="primary"
                icon="icon-plus"
                circle
                class="btn-add btn-add-main"
                @click.native.stop="togglePopupAddButtons"
                :class="{ 'popup-open': showPopupAddButtons }"
            ></at-button>
        </div>
        <at-modal v-model="uploadModal" :styles="{ width: '90%', maxWidth: '720px' }" :mask-closable="false" :show-close="uploadFinish">
            <div slot="header" style="text-align:center;">
                <span>{{uploadModalHeader}}</span>
            </div>

            <at-table :columns="uploadColumns" :data="uploadFilesData" :height="uploadModalTableHeight"></at-table>

            <div slot="footer"><at-button type="primary" @click="uploadModal = false" :disabled="!uploadFinish">关闭</at-button></div>
        </at-modal>
    </div>
</template>

<script>
import { axios } from '@/components/axios/api.js';
import { cos } from '@/components/cos/request.js';
export default {
    name: 'MyDisk',
    components: {},
    data() {
        return {
            innerHeight: 0,
            tableColumns: [
                {
                    title: '文件名',
                    key: 'name',
                    render: (h, params) => {
                        return params.item.type === 'folder'
                            ? h(
                                  'AtButton',
                                  {
                                      props: {
                                          type: 'text',
                                          icon: params.item.type === 'folder' ? 'icon-folder' : ''
                                      },
                                      on: {
                                          click: () => {
                                              this.goInsideFolder(params.item.name);
                                          }
                                      }
                                  },
                                  params.item.name
                              )
                            : h('Span', {}, params.item.name);
                    }
                },
                {
                    title: '大小',
                    key: 'size',
                    render: (h, params) => {
                        return params.item.type === 'folder' ? h('Span', {}, '') : h('Span', {}, params.item.size);
                    }
                },
                {
                    title: '上传时间',
                    key: 'created_at'
                }
            ],
            myfiles: [],
            key: 1,
            token: '',
            parent: '',
            showPopupAddButtons: false,
            lastParent: '',
            parentBreadcrumb: '',
            list: [],
            total: 0,
            uploadModal: false,
            uploadFinish: false,
            uploadModalTableHeight: 0,
            uploadModalTableWidth: 0,
            uploadColumns: [
                {
                    title: '文件名',
                    key: 'name'
                },
                {
                    title: '大小',
                    key: 'size'
                },
                {
                    title: '进度',
                    key: 'progress',
                    render: (h, params) => {
                        return h(
                            'div',
                            {
                                style: {
                                    width: `${this.uploadModalTableWidth*0.3}px`
                                }
                            },
                            params.item.progress
                        );
                    }
                }
            ],
            uploadFilesData: []
        };
    },
    mounted() {
        this.initialize();
        this.getMyFiles();
    },
    computed: {
        parentPaths() {
            return false;
        },
        parentArr() {
            return this.parentBreadcrumb.substr(1).split('/');
        },
        uploadModalHeader() {
            if (!this.uploadFinish) {
                if (this.total === 0) {
                    return '正在等待上传，请勿关闭窗口或浏览器'
                } else {
                    return '正在上传，共' + this.total + '个文件，请勿关闭窗口或浏览器'
                }
            } else {
                return '上传完成'
            }
        }
    },
    methods: {
        initialize() {
            this.innerHeight = window.innerHeight - 85 - 38 - 1; // 85是margin，38是breadcrumb的宽度，1是那根分割线
            this.uploadModalTableHeight = window.innerHeight * 0.5;
            this.uploadModalTableWidth = window.innerWidth * 0.9 >= 720 ? 720 : window.innerWidth * 0.9
            window.addEventListener('resize', () => {
                this.$nextTick(() => {
                    this.innerHeight = window.innerHeight - 85 - 38 - 1;
                    this.uploadModalTableHeight = window.innerHeight * 0.5;
                    this.uploadModalTableWidth = window.innerWidth * 0.9 >= 720 ? 720 : window.innerWidth * 0.9;
                });
            });
            // 点击按钮外部 隐藏“添加”按钮
            document.body.addEventListener(
                'click',
                () => {
                    this.showPopupAddButtons = false;
                },
                false
            );
            this.parent = '';
            this.parentBreadcrumb = '';
            if (!this.getToken()) {
                return false;
            }
            this.cos_client = cos(this.token);
            this.cos_client.on('list-update', data => {
                this.lastList = data.list // 记录一下每一次list，不然最后一次上传完成后，检查数据库之后没有办法继续更新table
                this.updateUploadModalData(data.list);
            });
        },
        chooseFileToUpload() {
            this.$refs.fileInput.click();
        },
        updateUploadModalData(list) {
            this.total = list.length;
            this.uploadFilesData = list.map((item, index) => {
                return {
                    name: item.Key.replace(/\/.+\//g, ''),
                    size: this.formatSize(item.size),
                    progress: this.formatProgress(item, index)
                };
            });
        },
        formatSize(size) {
            var i,
                unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
            for (i = 0; i < unit.length && size >= 1024; i++) {
                size /= 1024;
            }
            return (Math.round(size * 100) / 100 || 0) + unit[i];
        },
        formatProgress(item, index) {
            let state = item.state;
            switch (state) {
                case 'success':
                    if (this.toDatabase[index].error) {
                        return '与数据库校验出错，请在其他文件上传完成后重试'
                    }
                    if (this.toDatabase[index].finished) {
                        return '上传成功'
                    }
                    return '正在检查文件完整性';
                    break;
                case 'waiting':
                    return '等待上传';
                    break;
                case 'checking':
                    return `正在校验文件(${parseInt(item.hashPercent * 100)}%)`;
                    break;
                case 'paused':
                    return `已暂停, 已传${this.formatSize(item.loaded)}`;
                    break;
                case 'canceled':
                    return '已取消';
                    break;
                default:
                    // this.cos_client.cancelTask(item.id);
                    return `${this.formatSize(item.speed)}/s, 已传${this.formatSize(item.loaded)} ${parseInt(item.percent * 100)}%`;
                    break;
            }
        },
        updateToDatabase(id, key) {
            return new Promise((resolve, reject) => {
                axios(
                    `disk/${id}`,
                    'PATCH',
                    {
                        absolute_location: key
                    },
                    this.token
                )
                    .then(response => {
                        resolve(response.data);
                    })
                    .catch(error => {
                        // this.parent = this.lastParent;
                        this.$Message.error(error.data ?? '遇到未知错误');
                        resolve(error.data);
                    });
            });
        },
        storeToDatabase(files) {
            return new Promise((resolve, reject) => {
                axios(
                    'disk',
                    'POST',
                    {
                        files: files,
                        parent: this.parent,
                        type: 'file'
                    },
                    this.token
                )
                    .then(response => {
                        resolve(response.data);
                    })
                    .catch(error => {
                        // this.parent = this.lastParent;
                        this.$Message.error(error.data ?? '遇到未知错误');
                        resolve(error.data);
                    });
            });
        },
        async handleUploadFile(e) {
            let that = this
            let config = {};
            let height = (await window.innerHeight) * 0.5;
            config.Bucket = 'frnetdisk-1251693677';
            config['Region'] = 'ap-shanghai';
            let username = this.getCookies('username');
            if (username === null || username === undefined) {
                this.$Message.error({
                    message: '登陆失效，请重新登录！',
                    duration: 5000
                });
            }

            let fileToUpload = [];
            this.toDatabase = [];
            let md5 = require('md5');
            for (let file of e.target.files) {
                if (!file) {
                    continue;
                }
                let name = file.name;
                let size = file.size;
                if (name.indexOf('/') !== -1) {
                    continue;
                }
                fileToUpload.push({
                    Bucket: config.Bucket,
                    Region: config.Region,
                    Key: '/' + username + this.parent + '/' + name,
                    Body: file
                });
                this.toDatabase.push({
                    name: name,
                    size: size,
                    iid: md5(name),
                    finished: false
                });
            }
            if (this.toDatabase.length === 0) {
                return false
            }
            
            
            // 显示表格，预填充数据
            this.uploadFinish = false
            this.uploadModal = true;
            // 不重设表格高度，则表格无法正常显示，怀疑是at-ui的bug
            this.uploadModalTableHeight = 200;
            this.$nextTick(() => {
                this.uploadModalTableHeight = height;
            });

            this.uploadFilesData = this.toDatabase.map(value => {
                return {
                    name: value.name,
                    size: this.formatSize(value.size),
                    progress: '等待上传'
                };
            });
            // 先插入数据库
            let res = await this.storeToDatabase(this.toDatabase);
            if (!(res instanceof Object) || res.msg !== '创建成功'){
                return false;
            }
            
            // 获取返回的id
            let idDict = res.data
            for (let item of this.toDatabase) {
                item['id'] = idDict[item['iid']]
            }
            this.cos_client.uploadFiles(
                {
                    files: fileToUpload,
                    SliceSize: 1024 * 1024,
                    // onProgress: function(info) {
                    //     var percent = parseInt(info.percent * 10000) / 100;
                    //     var speed = parseInt((info.speed / 1024 / 1024) * 100) / 100;
                    //     console.log('进度：' + percent + '%; 速度：' + speed + 'Mb/s;');
                    // },
                    onFileFinish: async function(err, data, options) {
                        if (!err) {
                            let keyIndex = parseInt(options.Index)
                            let uploadFinalResult = await that.updateToDatabase(that.toDatabase[keyIndex].id, options.Key)
                            if (!(uploadFinalResult instanceof Object) || uploadFinalResult.msg !== '成功'){
                                // 不知为何，最后存absolute_location的时候失败了
                                that.toDatabase[keyIndex].error = true
                            } else {
                                that.toDatabase[keyIndex].finished = true
                            }
                            that.updateUploadModalData(that.lastList)
                            
                            var allfinish = true
                            for (let i of that.toDatabase) {
                                if (!i.error && !i.finished) {
                                    allfinish = false
                                    break
                                }
                            }
                            if (allfinish) {
                                that.uploadFinish = true
                                that.getMyFiles();
                            }
                        } else {
                            console.log(options.Key + ' 上传' + (err ? '失败' : '完成'));
                        }
                    }
                },
                function(err, data) {
                    that.updateUploadModalData(that.lastList)
                }
            );
        },
        goOutsideFolder(folderLevelIndex) {
            let arr = this.parentArr;
            // 如果点击了home，并且现在不在主页，那么回主页
            if (folderLevelIndex === -1) {
                if (arr[0] !== '') {
                    this.lastParent = this.parent;
                    this.parent = '';
                    this.getMyFiles();
                }
                return false;
            }

            // 如果点击的是最后一项，不跳转
            if (folderLevelIndex === arr.length - 1) {
                return false;
            }

            this.lastParent = this.parent;
            arr.splice(folderLevelIndex + 1);
            this.parent = '/' + arr.join('/');
            this.getMyFiles();
        },
        goInsideFolder(folderName) {
            this.lastParent = this.parent;
            this.parent += '/' + folderName;
            this.getMyFiles();
        },
        getCookies(key) {
            return this.$cookies.get(key) ?? null;
        },
        getToken() {
            let token = this.getCookies('token');
            if (token) {
                this.token = token;
                return true;
            } else {
                this.$Message.error({
                    message: '登陆失效，请重新登录！',
                    duration: 5000
                });
            }
        },
        getMyFiles() {
            axios('disk', 'GET', {}, this.token, { path: this.parent })
                .then(response => {
                    this.parentBreadcrumb = this.parent;
                    this.myfiles = response.data.map(value => {
                        value.created_at = new Date(Date.parse(value.created_at)).toLocaleString('zh', { hour12: false });
                        value.size = Math.round((value.size * 100) / 1024) / 100 + 'KB';
                        return value;
                    });
                })
                .catch(error => {
                    this.parent = this.lastParent;
                    this.$Message.error(error.data);
                })
                .then(() => {});
        },
        togglePopupAddButtons() {
            this.showPopupAddButtons = !this.showPopupAddButtons;
        },
        handleAddFolder() {
            this.$Modal
                .prompt({
                    title: '提示',
                    content: '请输入新建文件夹名称：',
                    width: window.innerWidth >= 577.78 ? 520 : window.innerWidth * 0.9
                })
                .then(data => {
                    if (data.value && data.value.replace(/\ /g, '') !== '') {
                        this.createItem(data.value, 'folder');
                    }
                })
                .catch(() => {});
        },
        createItem(itemName, type) {
            axios('disk', 'POST', { name: itemName, type: type, parent: this.parent }, this.token)
                .then(response => {
                    this.$Message.success({
                        message: response.data,
                        duration: 2000
                    });
                    this.getMyFiles();
                })
                .catch(error => {
                    this.$Message.error(error.data);
                })
                .then(() => {});
        }
    }
};
</script>

<style scoped lang="scss">
.slide-fade-enter-active {
    transition: all 0.3s ease;
}
.slide-fade-leave-active {
    transition: all 0.3s ease;
}
.slide-fade-enter, .slide-fade-leave-to
    /* .slide-fade-leave-active for below version 2.1.8 */ {
    transform: translateY(56px);
    opacity: 0;
}
.breadcrumb {
    text-align: left;
    margin-left: 20px;
}
.breadcrumb::after {
    content: '';
    display: block;
    width: calc(100% + 20px);
    height: 0px;
    margin-top: 5px;
    margin-left: -20px;
    border: 1px solid #52b2bc;
}
.at-breadcrumb__item {
    font-size: 1.5em;
    color: #52b2bc;
}
.at-breadcrumb__item:last-child {
    color: #a6babc;
}
::v-deep .at-table__cell > .at-btn--text {
    padding-left: 0;
}
.add-button-wrapper {
    position: absolute;
    bottom: 25px;
    right: 25px;
    z-index: 99;
    width: 48px;
    .btn-add {
        width: 48px;
        height: 48px;
        margin-top: 8px;
        transition: all 0.3s ease 0.08s;
        ::v-deep .at-btn__icon {
            font-size: 24px;
        }
    }
    .popup-open {
        transform: rotate(-135deg);
        transition: all 0.3s ease 0.08s;
    }
}
@media screen and (max-width: 1023px) {
    .mydisk {
        margin-top: 20px;
    }
    // TODO: 小屏幕的时候，表格的高度需要调整 重新算
    .add-button-wrapper {
        bottom: 5px;
        right: 10px;
        width: 52px;
        .btn-add {
            width: 52px;
            height: 52px;
        }
    }
}
</style>
