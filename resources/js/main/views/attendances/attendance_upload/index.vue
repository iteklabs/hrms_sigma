<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.attendances`) + ` Upload`" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.attendances`) }} Upload
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.attendances`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>


    <admin-page-filters>
        <a-row :gutter="[16, 16]">
            <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
                <a-space>
                    <template
                        v-if="
                            permsArray.includes('attendance_upload_add') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-button type="primary" @click="addItem">
                            <PlusOutlined />
                            Add New Scheduled
                        </a-button>
                    </template>



                    <template
                        v-if="
                            permsArray.includes('attendance_upload_add') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-button type="primary" href="/download_schedule_template" target="_blank">
                            <DownloadOutlined />
                            Download Migration File Scheduled
                        </a-button>

                        <a-upload
                            
                            :beforeUpload="beforeUpload"
                            :fileList="fileList"
                            :showUploadList="false"
                            accept=".xlsx,.xls"
                        >
                            <a-button 
                            :loading="uploadLoading"
                            type="primary" >
        
                                <UploadOutlined />
                                Click to Choose Excel File
                            </a-button>
                        </a-upload>

                        <a-button
                            type="primary"
                            :loading="uploadLoading"
                            :disabled="!selectedFile || uploadLoading"
                            @click="uploadFile"
                        >
                        <CheckOutlined />
                        Upload Schedule
                        </a-button>


                        <div v-if="selectedFile" style="margin-top: 8px;">
                            Selected File: {{ selectedFile.name }}
                        </div>

                        <a-modal
                            v-model:visible="isModalVisible"
                            title="Schedule Upload Summary"
                            @ok="handleOk"
                            @cancel="handleCancel"
                            :width="1000"
                        >
                        <a-table
                            :columns="columns_data"
                            :data-source="previewData"
                            :pagination="false"
                            row-key="id"
                            bordered
                        >
                        <template #bodyCell="{ column, record }">
                            <!-- <h1>{{ record.location_name + " <> " + column.dataIndex }}</h1> -->
                            <template v-if="column.dataIndex === 'employee_name'">
                                {{ record.name }}
                            </template>
                            <template v-if="column.dataIndex === 'date'">
                                {{ formatDate(record.date) }}
                            </template>
                            <template v-if="column.dataIndex === 'date_to'">
                                {{ formatDate(record.date_to) }}
                            </template>
                            <template v-if="column.dataIndex === 'time_in'">
                                {{ formatTime(record.date+' '+record.time_in+':00:00') }}
                            </template>
                            <template v-if="column.dataIndex === 'time_out'">
                                {{ formatTime(record.date_to+' '+record.time_out+':00:00') }}
                            </template>
                            <template v-if="column.dataIndex === 'schedule_type'">
                                {{ record.scheduled_type }}
                            </template>
                        
                            <template v-if="column.dataIndex === 'location_name'">
                                {{ record.location_name }}
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <span v-if="record.status === 'Found!'" style="color: green">
                                <CheckCircleOutlined /> {{ record.status }}
                                </span>
                                <span v-else style="color: red">
                                <CloseCircleOutlined /> {{ record.status }}
                                </span>
                            </template>
                        </template>
                        </a-table>
                        </a-modal>


                    </template>
                </a-space>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
                <a-row :gutter="[16, 16]" justify="end">
                    <a-col
                        v-if="
                            permsArray.includes('attendance_upload_view') ||
                            permsArray.includes('admin')
                        "
                        :xs="24"
                        :sm="24"
                        :md="12"
                        :lg="12"
                        :xl="8"
                    >
                        <a-select
                            v-model:value="extraFilters.user_id"
                            @change="setUrlData"
                            show-search
                            style="width: 100%"
                            :placeholder="
                                $t('common.select_default_text', [$t('attendance.user')])
                            "
                            :allowClear="true"
                            optionFilterProp="title"
                        >
                            <a-select-option
                                v-for="user in users"
                                :key="user.xid"
                                :value="user.xid"
                                :title="user.name"
                            >
                                <user-list-display :user="user" whereToShow="select" />
                            </a-select-option>
                        </a-select>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12" :xl="8">
                        <a-range-picker
                            v-model:value="extraFilters.date"
                            valueFormat="YYYY-MM-DD"
                            style="width: 100%"
                            @change="setUrlData"
                        />
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </admin-page-filters>

    <admin-page-table-content>
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
            @addListSuccess="reSetFormData"
        />

        <a-row>
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :row-selection="{
                            selectedRowKeys: table.selectedRowKeys,
                            onChange: onRowSelectChange,
                            getCheckboxProps: (record) => ({
                                disabled: false,
                                name: record.xid,
                            }),
                        }"
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        bordered
                        size="middle"
                    >
                        <template #bodyCell="{ column, record }">
                            <!-- <pre>{{ record }}</pre> -->

                            <template v-if="column.dataIndex === 'user_id'">
                                <a-button type="link" @click="openUserView(record)">
                                    <UserInfo :user="record.user" />
                                </a-button>
                            </template>

                            <template v-if="column.dataIndex === 'date'">
                                {{ formatDate(record.date) }}
                            </template>
                            <template v-if="column.dataIndex === 'date_to'">
                                {{ formatDate(record.date_to) }}
                            </template>
                            <template v-if="column.dataIndex === 'time_in'">
                                {{ formatTime(formatDate(record.date) + ' ' + record.time_in) }}
                            </template>
                            <template v-if="column.dataIndex === 'time_out'">
                                {{ formatTime(formatDate(record.date) + ' ' + record.time_out) }}
                            </template>
                            <template v-if="column.dataIndex === 'schedule_type'">
                                <template v-if="record.schedule_type === 'shift'">
                                    Main Shift
                                </template>

                                <template v-if="record.schedule_type === 'OVD'">
                                    Override Shift
                                </template>

                                <template v-if="record.schedule_type === 'RVR'">
                                    Reliever
                                </template>
                            </template>
                            <template v-if="column.dataIndex === 'schedule_location_id'">
                                {{ record.schedule_location.name }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        (permsArray.includes('attendance_upload_edit') ||
                                            permsArray.includes('admin'))
                                    "
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="
                                        permsArray.includes('attendance_upload_delete') ||
                                        permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="showDeleteConfirm(record.xid)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><DeleteOutlined /></template>
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </admin-page-table-content>
    <user-view-page :visible="userOpen" :userId="userId" @closed="closeUser" />
</template>

<script>
import { CheckCircleOutlined, CheckOutlined, DeleteOutlined, DownloadOutlined, EditOutlined, PlusOutlined, UploadOutlined } from "@ant-design/icons-vue";
import { message, Modal } from 'ant-design-vue';
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import UserListDisplay from "../../../../common/components/user/UserListDisplay.vue";
import common from "../../../../common/composable/common";
import crud from "../../../../common/composable/crud";
import hrmManagement from "../../../../common/composable/hrmManagement";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import AddEdit from "./AddEdit.vue";
import fields from "./fields";
export default {
    components: {
        AdminPageHeader,
        PlusOutlined,
        CheckCircleOutlined,
        DeleteOutlined,
        EditOutlined,
        DownloadOutlined,
        UserInfo,
        UserListDisplay,
        UploadOutlined ,
        CheckOutlined,
        AddEdit
    },
    setup() {
        const { t } = useI18n();
        const reprocessLoading = ref(false);
        const {
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            url,
            hashableColumns,
            columns_data
        } = fields();
        
        const {
            permsArray,
            appSetting,
            formatDate,
            formatTime,
            formatDateTime,
            
        } = common();
        const { formatMinutes } = hrmManagement();
        const userOpen = ref(false);
        const userId = ref(undefined);
        const uploadLoading = ref(false);
        const isModalVisible  = ref(false);
        const crudVariables = crud();
        const users = ref([]);
        const previewData = ref(null);
        
        const userUrl = "users?limit=10000";
        const extraFilters = ref({
            user_id: undefined,
            date: undefined,
            status: "all",
        });

        const openUserView = (item) => {
            // console.log(item.x_user_id)
            userId.value = item.x_user_id;
            userOpen.value = true;
        };

        const closeUser = () => {
            userId.value = undefined;
            userOpen.value = false;
        };
        const selectedFile = ref(null);
        const fileList = ref([]);
        
        const beforeUpload = (file) => {
            const isExcel = file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
                            file.type === 'application/vnd.ms-excel'
            selectedFile.value = file;
            fileList.value = [file]; // keep only 1 file
            if (!isExcel) {
                message.error('Only .xlsx or .xls files are allowed!')
                return false
            }

            selectedFile.value = file
            fileList.value = [file]
            return false // Prevent auto upload
        }
        const uploadFile = async () => {
            if (!selectedFile.value) return
           
            const formData = new FormData()
            formData.append('file', selectedFile.value)
            // uploadLoading.value = true;
            try {
                // // Show modal/preview here
                uploadLoading.value = true;
                
                axiosAdmin.post("attendances/preview", formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then((response) => {
                    message.success('Preview ready!');
                    previewData.value = response.data;
                    isModalVisible .value = true;
                })
                .catch((error) => {
                    message.error('Failed to upload file');
                })
                .finally(() => {
                    uploadLoading.value = false;
                    selectedFile.value = null;
                });

            } catch (error) {
                message.error('Failed to upload file')
            } 
        }

        const handleOk = () => {
            // isModalVisible.value = false;
            Modal.confirm({
                title: 'Confirm Schedule Save',
                content: 'Are you sure you want to save this data for schedule?',
                okText: 'Yes, Save it',
                cancelText: 'Cancel',
                onOk() {
                // ✅ Your save logic here
                saveScheduleData()
                },
                onCancel() {
                // Optional: do something on cancel
                }
            })
        };

        const saveScheduleData = () => {
            // Replace this with your actual save logic (API call, emit, etc.)
            const trueOnly = previewData.value.filter(item => item.bool === true);

            axiosAdmin
                .post("attendances/saved", {
                    data : trueOnly
                })
                .then((response) => {
                    isModalVisible.value = false
                    setUrlData();
                    message.success('Schedule data saved successfully!');
                })
            // console.log(trueOnly)

           
        }

        const handleCancel = () => {
            isModalVisible.value = false;
        };


        onMounted(() => {
            if (
                permsArray.value.includes("attendance_upload_edit") ||
                permsArray.value.includes("attendance_upload_delete") ||
                permsArray.value.includes("admin")
            ) {
                columns.value = [
                    ...columns.value,
                ];
            }

            getUsers();
            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                extraFilters,
            };
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "attendance";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = [...hashableColumns];
            crudVariables.table.sorter = { field: "date", order: "desc" };

            crudVariables.fetch({
                page: 1,
            });
        };


        const getUsers = () => {
            const usersPromise = axiosAdmin.get(userUrl);
            Promise.all([usersPromise]).then(([usersResponse]) => {
                users.value = usersResponse.data;
            });
        };

        return {
            permsArray,
            extraFilters,
            columns,
            selectedFile,
            reprocessLoading,
            users,
            isModalVisible,
            openUserView,
            closeUser,
            ...crudVariables,
            uploadLoading,
            previewData,
            columns_data,
            uploadFile,
            beforeUpload,
            handleOk,
            handleCancel,
            formatDate,
            formatTime,
            userOpen,
            userId
        };
    }

}
</script>