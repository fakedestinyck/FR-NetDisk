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
                        :key="index"
                    >
                        {{ path }}
                    </at-breadcrumb-item>
                    <!-- <at-breadcrumb-item :to="{ name: 'Color-zh' }" replace>Color</at-breadcrumb-item> -->
                </template>
            </at-breadcrumb>
        </div>
        <input type="file" v-show="false" ref="fileInput" @change="handleUploadFile" multiple />
        <!-- <at-table :columns="columns1" :data="data2" optional></at-table> -->
        <!-- <at-table :columns="tableColumns" :data="myfiles" :height="innerHeight" optional></at-table> -->
        <a-table :data-source="myfiles" :scroll="{ y: innerHeight }" :pagination="false" rowKey="id" size="middle" :row-selection="fileRowSelection">
            <a-table-column key="name" title="文件名" data-index="name" :ellipsis="true">
                <template slot-scope="name, data">
                    <a-button type="link" icon="folder" size="small" @click="goInsideFolder(name)" v-if="data.type === 'folder'">{{ name }}</a-button>
                    <a-button type="link" size="small" @click="goInsideFolder(name)" v-else>{{ name }}</a-button>
                </template>
            </a-table-column>

            <a-table-column key="size" title="大小" data-index="size" :ellipsis="true">
                <template slot-scope="size">
                    <span>{{ size }}</span>
                </template>
            </a-table-column>
            <a-table-column key="created_at" title="上传日期" data-index="created_at" :ellipsis="true" />

            <a-table-column key="action" title="操作" :ellipsis="true">
                <template slot-scope="text, data">
                    <span>
                        <a-button type="primary" size="small" @click="handleShareFiles([data])">分享</a-button>
                        <a-divider type="vertical" />
                        <a-button type="danger" size="small" @click="handleDeleteFiles([data])">删除</a-button>
                    </span>
                </template>
            </a-table-column>
        </a-table>

        <div class="add-button-wrapper">
            <transition name="slide-fade">
                <div class="btn-add-popup-wrapper" v-show="showPopupAddButtons">
                    <a-button type="primary" icon="folder-add" shape="circle" class="btn-add btn-add-popup" @click="handleAddFolder"></a-button>
                    <a-button type="primary" icon="file-add" shape="circle" class="btn-add btn-add-popup" @click="chooseFileToUpload"></a-button>
                </div>
            </transition>
            <a-button
                type="primary"
                icon="plus"
                shape="circle"
                class="btn-add btn-add-main"
                @click.native.stop="togglePopupAddButtons"
                :class="{ 'popup-open': showPopupAddButtons }"
            ></a-button>
        </div>
        <at-modal v-model="uploadModal" :styles="{ width: '90%', maxWidth: '720px' }" :mask-closable="false" :show-close="uploadFinish" :closeOnPressEsc="uploadFinish">
            <div slot="header">
                <span>{{ uploadModalHeader }}</span>
            </div>

            <a-table :data-source="uploadFilesData" :scroll="{ y: uploadModalTableHeight }" :pagination="false" rowKey="iid" size="middle">
                <a-table-column key="name" title="文件名" data-index="name" :ellipsis="true">
                    <template slot-scope="name">
                        <span>{{ name }}</span>
                    </template>
                </a-table-column>

                <a-table-column key="size" title="大小" data-index="size" :ellipsis="true" width="82px">
                    <template slot-scope="size">
                        <span>{{ size }}</span>
                    </template>
                </a-table-column>

                <a-table-column key="progress" title="进度" data-index="progress" :ellipsis="true" width="232px">
                    <template slot-scope="progress">
                        <span>{{ progress }}</span>
                    </template>
                </a-table-column>
            </a-table>

            <div slot="footer"><at-button type="primary" @click="uploadModal = false" :disabled="!uploadFinish">关闭</at-button></div>
        </at-modal>

        <!-- 分享文件modal -->
        <at-modal
            v-model="shareModal"
            :styles="{ width: '90%', maxWidth: '400px' }"
            :mask-closable="false"
            :show-close="!requestSharing"
            :closeOnPressEsc="!requestSharing"
        >
            <div slot="header" style="text-align:center;"><span>分享文件</span></div>

            <template v-if="!sharingFinished && !requestSharing">
                <h3>选择分享到期日期</h3>
                <a-calendar
                    :fullscreen="false"
                    @select="onCalendarSelect"
                    valueFormat="X"
                    :disabledDate="setCalendarDisabledDate"
                    :validRange="[moment(), moment().add(364, 'd')]"
                />
            </template>
            <a-skeleton :loading="requestSharing" v-else active>
                <div>
                    <a-result status="error" title="分享失败" sub-title="可以尝试过几分钟重试" v-if="shareLink === ''" />
                    <a-result status="success" title="分享成功" sub-title="链接:" v-else>
                        <at-input v-model="shareLink" readonly>
                            <template slot="prepend">
                                <i class="icon icon-link"></i>
                            </template>
                        </at-input>
                        <a-button v-if="showCopyButton" type="primary" v-clipboard:copy="shareLink" v-clipboard:success="onCopySuccess" v-clipboard:error="onCopyError">复制并关闭</a-button>
                    </a-result>
                </div>
            </a-skeleton>

            <div slot="footer">
                <template v-if="!sharingFinished">
                    <a-button @click="shareModal = false" v-if="!requestSharing" style="margin-right: 10px;">取消</a-button>
                    <a-button type="primary" icon="link" :loading="requestSharing" @click="shareFiles" :disabled="!selectedShareEndDate">确认分享</a-button>
                </template>
                <a-button type="primary" @click="shareModal = false" v-else>关闭</a-button>
            </div>
        </at-modal>
    </div>
</template>

<script>
import { axios } from '@/components/axios/api.js';
import { cos } from '@/components/cos/request.js';
import moment from 'moment';
export default {
    name: 'MyDisk',
    components: {},
    data() {
        return {
            innerHeight: 0,
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
            uploadFilesData: [],
            shareModal: false,
            moment,
            requestSharing: false,
            sharingFinished: false,
            selectedShareEndDate: null,
            shareLink: '',
            showCopyButton: true
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
                    return '正在等待上传，请勿关闭窗口或浏览器';
                } else {
                    return '正在上传，共' + this.total + '个文件，请勿关闭窗口或浏览器';
                }
            } else {
                return '上传完成';
            }
        },
        fileRowSelection() {
            return {
                onChange: (selectedRowKeys, selectedRows) => {
                    console.log(`selectedRowKeys: ${selectedRowKeys}`, 'selectedRows: ', selectedRows);
                },
                getCheckboxProps: record => ({
                    props: {
                        disabled: record.name === 'Disabled User', // Column configuration not to be checked
                        name: record.name
                    }
                })
            };
        }
    },
    methods: {
        initialize() {
            this.innerHeight = window.innerHeight - 85 - 38 - 1 - 46; // 85是margin，38是breadcrumb的宽度，1是那根分割线，46是表格头
            this.uploadModalTableHeight = window.innerHeight * 0.5 - 46;
            this.uploadModalTableWidth = window.innerWidth * 0.9 >= 720 ? 720 : window.innerWidth * 0.9;
            window.addEventListener('resize', () => {
                this.$nextTick(() => {
                    this.innerHeight = window.innerHeight - 85 - 38 - 1 - 46;
                    this.uploadModalTableHeight = window.innerHeight * 0.5 - 46;
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
                this.lastList = data.list; // 记录一下每一次list，不然最后一次上传完成后，检查数据库之后没有办法继续更新table
                this.updateUploadModalData(data.list);
            });
        },
        setCalendarDisabledDate(date) {
            if (!date) {
                return false;
            }
            return date < moment().subtract(1, 'd');
        },
        onCalendarSelect(value) {
            this.selectedShareEndDate = value;
        },
        shareFiles() {
            if (!this.selectedShareEndDate) {
                return false;
            }
            this.requestSharing = true;
            axios(
                'shared',
                'POST',
                {
                    ids: this.fileidsToShare,
                    expired_at: this.selectedShareEndDate
                },
                this.token
            )
                .then(response => {
                    this.$Message.success(response.data.msg);
                    this.shareLink = `${process.env.BASE_URL}/shared/${response.data.data}`;
                })
                .catch(error => {
                    // this.parent = this.lastParent;
                    this.$Message.error(error.data ?? '遇到未知错误');
                })
                .then(() => {
                    this.sharingFinished = true;
                    this.requestSharing = false;
                });
        },
        handleShareFiles(files) {
            if (files.length === 0) {
                return false;
            }
            this.shareLink = '';
            this.sharingFinished = false;
            this.shareModal = true;
            this.selectedShareEndDate = moment().format('X');
            this.fileidsToShare = files.map(file => {
                return file.id;
            });
        },
        handleDeleteFiles(files) {
            let that = this;
            if (files.length === 0) {
                return false;
            }
            let title = `确认要删除 ${files[0].name} `;
            if (files.length > 1) {
                title += `等 ${files.length} 个文件/文件夹吗？`;
            } else {
                title += '吗？';
            }
            this.$confirm({
                title: title,
                content: (h, params) => {
                    return h(
                        'div',
                        {
                            style: {
                                color: 'red'
                            }
                        },
                        '文件将在回收站中保存24小时，之后删除操作将不能被撤销！'
                    );
                },
                centered: true,
                cancelText: '取消',
                okText: '删除',
                okType: 'danger',
                onOk() {
                    return that.deleteFiles(
                        files.map(file => {
                            return file.id;
                        })
                    );
                },
                onCancel() {}
            });
        },
        deleteFiles(fileids) {
            return new Promise((resolve, reject) => {
                axios(
                    'disk',
                    'DELETE',
                    {
                        ids: fileids
                    },
                    this.token
                )
                    .then(response => {
                        this.$Message.success(response.data.msg);
                        resolve();
                    })
                    .catch(error => {
                        // this.parent = this.lastParent;
                        this.$Message.error(error.data ?? '遇到未知错误');
                        resolve();
                    });
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
                    progress: this.formatProgress(item, index),
                    iid: this.toDatabase[index].iid
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
                        return '与数据库校验出错，请在其他文件上传完成后重试';
                    }
                    if (this.toDatabase[index].finished) {
                        return '上传成功';
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
            let that = this;
            let config = {};
            let height = (await window.innerHeight) * 0.5 - 46;
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
                return false;
            }

            // 显示表格，预填充数据
            this.uploadFinish = false;
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
                    progress: '等待上传',
                    iid: value.iid
                };
            });

            // 先插入数据库
            let res = await this.storeToDatabase(this.toDatabase);
            if (!(res instanceof Object) || res.msg !== '创建成功') {
                return false;
            }

            // 获取返回的id
            let idDict = res.data;
            for (let item of this.toDatabase) {
                item['id'] = idDict[item['iid']];
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
                            let keyIndex = parseInt(options.Index);
                            let uploadFinalResult = await that.updateToDatabase(that.toDatabase[keyIndex].id, options.Key);
                            if (!(uploadFinalResult instanceof Object) || uploadFinalResult.msg !== '成功') {
                                // 不知为何，最后存absolute_location的时候失败了
                                that.toDatabase[keyIndex].error = true;
                            } else {
                                that.toDatabase[keyIndex].finished = true;
                            }
                            that.updateUploadModalData(that.lastList);

                            var allfinish = true;
                            for (let i of that.toDatabase) {
                                if (!i.error && !i.finished) {
                                    allfinish = false;
                                    break;
                                }
                            }
                            if (allfinish) {
                                that.uploadFinish = true;
                                that.getMyFiles();
                            }
                        } else {
                            console.log(options.Key + ' 上传' + (err ? '失败' : '完成'));
                        }
                    }
                },
                function(err, data) {
                    that.updateUploadModalData(that.lastList);
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
        },
        onCopySuccess(e) {
            this.$Message.success('复制成功！');
            this.shareModal = false
        },
        onCopyError(e) {
            console.log(e)
            this.$Message.error('复制失败，请尝试手动复制');
            this.showCopyButton = false
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
::v-deep td > .ant-btn-link {
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
        ::v-deep .anticon {
            font-size: 24px;
            height: 24px;
            margin-left: 1px;
        }
        ::v-deep .anticon-plus {
            margin-left: 0;
        }
    }
    .popup-open {
        transform: rotate(-135deg);
        transition: all 0.3s ease 0.08s;
    }
}
::v-deep .ant-result-content {
    padding: 0;
    background-color: transparent;
    button {
        margin-top: 16px;
    }
}
.ant-result {
    padding: 16px 8px;
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
