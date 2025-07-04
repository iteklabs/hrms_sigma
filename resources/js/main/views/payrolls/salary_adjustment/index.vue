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
                            permsArray.includes('salary_adjustment') ||
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
                            <template v-if="column.dataIndex === 'name'">
                                <span
                                    v-if="
                                        record.name
                                    "
                                >{{ record.name }}</span>
                            </template>
                            <template v-if="column.dataIndex === 'date_from'">
                                <span>{{ new Date(record.date_from).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' }) }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'date_to'">
                                <span>{{ new Date(record.date_to).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' }) }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'amount'">
                                <span>{{ record.amount.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'type'">
                                <span v-if="record.type === 'T'">Taxable</span>
                                <span v-else-if="record.type === 'NT'">Non-Taxable</span>
                            </template>

                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('salary_adjustment') ||
                                        permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="modelOpen(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                            </template>

                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
        <AddEdit
            addEditType="edit"
            :visible="addEditVisible"
            :url="'salary_adjustment'"
            @addEditSuccess="addEditSuccess"
            @closed="modelClose"
            :formData="formData"
            :record="recordData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
            @addListSuccess="reSetFormData"
        />
    </admin-page-table-content>
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
            addEditUrl,
            statusColors,
            formatDateTime,
            user,
            formatAmountCurrency,
        } = common();
        const sampleFileUrl = window.config.staff_member_sample_file;
        const { t } = useI18n();
        const addEditVisible = ref(false);
        const recordData = ref("");
        const crudVariables = crud();
        const userOpen = ref(false);
        const userId = ref(undefined);

        const openUserView = (item) => {
            userId.value = item.xid;
            userOpen.value = true;
        };

        const closeUser = () => {
            userId.value = undefined;
            userOpen.value = false;
        };

        const extraFilters = ref({
            status: "active",
        });

        const closed = () => {
            addEditVisible.value = false;
        };
        const modelClose = () => {
            addEditVisible.value = false;
            setUrlData();
        };
        const modelOpen = (item) => {
            recordData.value = item;
            // console.log(item)
            addEditVisible.value = true;
        };

        const columns = [
            {
                title: t("salary_adjustment.name"),
                dataIndex: "name",
            },
            {
                title: t("salary_adjustment.date_from"),
                dataIndex: "date_from",
            },
            {
                title: t("salary_adjustment.date_to"),
                dataIndex: "date_to",
            },
            {
                title: t("salary_adjustment.amount"),
                dataIndex: "amount",
            },
            {
                title: t("salary_adjustment.type_taxable"),
                dataIndex: "type",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
        ];

        onMounted(() => {
            setUrlData();
        });

        const filterableColumns = [
            {
                key: "name",
                value: t("user.name"),
            },
        ];

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url:
                    "salary_adjustment?fields=id,name,process_payment,cut_off,month,year,date_from,date_to,amount,type",
                extraFilters,
            };

            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });
        };

        return {
            columns,
            filterableColumns,
            permsArray,
            statusColors,
            extraFilters,
            formatDateTime,
            ...crudVariables,
            sampleFileUrl,
            setUrlData,
            user,
            closed,
            modelOpen,
            addEditVisible,
            recordData,
            formatAmountCurrency,
            modelClose,
            userOpen,
            userId,
            openUserView,
            closeUser,
        };
    },
};
</script>
