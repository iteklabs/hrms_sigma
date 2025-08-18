<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.salary_deduction`)" class="p-0" />
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
                    {{ $t(`menu.salary_deduction`) }}
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
                            <a-button type="primary"  @click="addItemData('GovLoan')">
                                <PlusOutlined />
                                Add Government Loan
                            </a-button>
                            
                        </a-space>
                    </template>


                    <template 
                        v-if="
                            permsArray.includes('salary_adjustment_add_edit') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-space>
                            <a-button type="primary"  @click="addItemData('CompLoan')">
                                <PlusOutlined />
                                Add Company Loan
                            </a-button>
                            
                        </a-space>
                    </template>
                    
                </a-space>
            </a-col>
            
        </a-row>
    </admin-page-filters>

    <admin-page-table-content>
        <AddEditSSS
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
            :DataNeed="dataNeed"
        />
        <a-row>
            <a-col :span="24">
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
                        <template v-if="column.dataIndex === 'user_id'">
                            <a-button type="link" @click="openUserView(record)">
                                <UserInfo :user="record.user" />
                            </a-button>
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
            </a-col>
        </a-row>
    </admin-page-table-content>
    <user-view-page :visible="userOpen" :userId="userId" @closed="closeUser" />

</template>

<script>
import { DeleteOutlined, EditOutlined, PlusOutlined } from "@ant-design/icons-vue";
import { onMounted, ref } from "vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import common from "../../../../common/composable/common";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import AddEditSSS from "./AddEditSSS.vue";
import fields from "./fields";

import crud from "../../../../common/composable/crud";
export default {
    components: {
        AdminPageHeader,
        UserInfo,
        AddEditSSS,
        EditOutlined,
        PlusOutlined,
        DeleteOutlined
    },
    setup() {
        const {
            permsArray,
            formatAmountCurrency,
            dayjs
        } = common();

        
        const topTab = ref("GL"); // top-level tab
        const govLoanTab = ref("SSS"); // nested tab
        const userOpen = ref(false);
        const userId = ref(undefined);
        const crudVariables = crud();
        const dataNeed = ref('');
        const extraFilters = ref({
            status: "active",
        });
        const {
            url,
            initData,
            columns,
            addEditUrl,
            hashableColumns,
            filterableColumns
        } = fields();

        onMounted(() => {
            setUrlData();
        });

        function editItemInit(record){
            const NewData = { 
                ...record,
                start_year_specific: dayjs(record.start_year_specific, "YYYY"),
                start_month_specific: dayjs(record.start_month_specific, "MM"),
                DataNeed: record.loan_name,
                loan_name: record.loan_name
            };
            return NewData;
        }



            const addItemData = (type) => {
                dataNeed.value = type; // 'GovLoan' or 'CompLoan'
                crudVariables.addItem()
                // crudVariables.addEditType = "add";
                // crudVariables.addEditVisible = true;
                // crudVariables.url = `salary_deduction_loan`;
                // crud.addItem();
            };
        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url:url,
                extraFilters,
            }
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.fetch({
                page: 1,
            });

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "salary_deduction_loan";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = { ...hashableColumns };
        };

        const closeUser = () => {
            userId.value = undefined;
            userOpen.value = false;
        };

        return {
            topTab,
            govLoanTab,
            setUrlData,
            permsArray,
            closeUser,
            userOpen,
            userId,
            AddEditSSS,
            dataNeed,
            addItemData,
            ...crudVariables,
            columns,
            editItemInit
        };
    },
};
</script>
