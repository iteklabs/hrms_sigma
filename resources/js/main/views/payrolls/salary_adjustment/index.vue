<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.salary_adjustment`)" class="p-0" />
        </template>

        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.payrolls`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.salary_adjustment`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>

    </AdminPageHeader>

    <admin-page-filters>
        <a-row :gutter="[16, 16]">
            <a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
                <a-space>
                    <template
                        v-if="
                            permsArray.includes('salary_adjustment_add_edit') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-space>
                            <a-button type="primary" @click="addItem">
                                <PlusOutlined />
                                Add Salary adjustment
                            </a-button>
                            
                        </a-space>
                    </template>
                    
                </a-space>
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
                <a-tabs v-model:activeKey="extraFilters.status" @change="setUrlData">
                    <a-tab-pane key="all" :tab="`${$t('common.all')}`" />
                </a-tabs>
            </a-col>
        </a-row>

        <a-row>
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
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
                                <!-- <span
                                    v-if="
                                        record.user.name
                                    "
                                >{{ record.user.name }}</span> -->

                                <a-button type="link" @click="openUserView(record)">
                                    <UserInfo :user="record.user" />
                                </a-button>
                            </template>


                            <template v-if="column.dataIndex === 'name'">
                                <span
                                    v-if="
                                        record.name
                                    "
                                >{{ record.name }}</span>
                            </template>
                            

                            <template v-if="column.dataIndex === 'amount'">
                                <span>{{ record.amount.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'type'">
                                <span v-if="record.type === 'T'">Taxable</span>
                                <span v-else-if="record.type === 'NT'">Non-Taxable</span>
                            </template>

                            <template v-if="column.dataIndex === 'adjustment_type'">
                                <span v-if="record.adjustment_type === 'basic_pay'">Basic Pay</span>
                                <span v-else-if="record.adjustment_type === 'alowance_pay'">Allowance</span>
                                <span v-else-if="record.adjustment_type === 'overtime_pay'">Overtime</span>
                                <span v-else-if="record.adjustment_type === 'SPNWD_pay'">Special non-working day</span>
                                <span v-else-if="record.adjustment_type === 'rest_day_pay'">Rest Day</span>
                                <span v-else-if="record.adjustment_type === 'holiday_pay'">Holiday</span>
                            </template>

                            <template v-if="column.dataIndex === 'action'">
                                <a-space>
                                    <a-button
                                        v-if="
                                            permsArray.includes('salary_adjustment_add_edit') ||
                                            permsArray.includes('admin')
                                        "
                                        type="primary"
                                        @click="editItem(editItemInit(record))"
                                        style="margin-left: 4px"
                                    >
                                        <template #icon><EditOutlined /></template>
                                    </a-button>

                                    <a-button
                                        v-if="
                                            permsArray.includes('payrolls_delete') ||
                                            permsArray.includes('admin')
                                        "
                                        type="primary"
                                        @click="showDeleteConfirm(record.xid)"
                                    >
                                        <template #icon><DeleteOutlined /></template>
                                    </a-button>

                                </a-space>
                                

                                
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
import { DeleteOutlined, EditOutlined, PlusOutlined } from "@ant-design/icons-vue";
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import common from "../../../../common/composable/common";
import crud from "../../../../common/composable/crud";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import AddEdit from "./AddEdit.vue";
import fields from "./fields";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AdminPageHeader,
        AddEdit,
        UserInfo,
    },
    setup() {
        const {
            permsArray,
            formatAmountCurrency,
            dayjs
        } = common();

        const {
            url,
            initData,
            columns,
            addEditUrl,
            hashableColumns,
            filterableColumns
        } = fields();
        const sampleFileUrl = window.config.staff_member_sample_file;
        const { t } = useI18n();
        const recordData = ref("");
        const crudVariables = crud();
        const userOpen = ref(false);
        const userId = ref(undefined);

        const openUserView = (item) => {
            userId.value = item.x_user_id;
            userOpen.value = true;
        };

        const closeUser = () => {
            userId.value = undefined;
            userOpen.value = false;
        };

        const extraFilters = ref({
            status: "active",
        });

        function editItemInit(record){
            const NewData = { 
                ...record,
                start_year_specific: dayjs(record.start_year_specific, "YYYY"),
                start_month_specific: dayjs(record.start_month_specific, "MM"),
                end_year_specific: dayjs(record.end_year_specific, "YYYY"),
                end_month_specific: dayjs(record.end_month_specific, "MM"),
                month_specific: dayjs(record.month_specific, "MM"),
                year_specific: dayjs(record.year_specific, "YYYY"),
            };
            // console.log('editItemInit', NewData);
            return NewData;
            // editItem(NewData);
            // emit('editItem', props);
            // console.log('editItemInit', props);
        }

        onMounted(() => {
            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url:
                    url,
                extraFilters,
            };

            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });
            // console.log('initData', initData);
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "salary_adjustment";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = { ...hashableColumns };
        };
        return {
            columns,
            filterableColumns,
            permsArray,
            extraFilters,
            ...crudVariables,
            sampleFileUrl,
            setUrlData,
            closed,
            recordData,
            formatAmountCurrency,
            userOpen,
            userId,
            openUserView,
            closeUser,
            editItemInit
        };
    },
};
</script>
