<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >

        <a-form layout="vertical" id="add_edit_user_form">
            <a-tabs v-model:activeKey="addEditActiveTab">
                <a-tab-pane key="adjustment" :tab="$t('menu.salary_adjustment')">
                    
                    <a-row :gutter="16">
                        
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.name')"
                                name="input_text"
                            >
                                <a-input v-model:value="formData.name" />
                            </a-form-item>
                        </a-col>

                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.process_type')"
                                name="process_type"
                            >
                                <a-select
                                    v-model:value="formData.process_payment"
                                    style="width: 100%"
                                    :placeholder="$t('salary_adjustment.process_type')"
                                >
                                    <a-select-option
                                        value="recurring"
                                    >
                                        {{ $t('salary_adjustment.recurring') }}
                                    </a-select-option>
                                    <a-select-option value="date_range">
                                        {{ $t('salary_adjustment.date_range') }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>

                        <!-- Recurring Options -->
                        <template v-if="formData.process_payment === 'recurring'">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item :label="$t('salary_adjustment.recurring')" name="recurring_type">
                                    <a-row :gutter="8">
                                        <a-col :span="8">
                                            <a-input
                                                v-model:value="formData.year"
                                                :placeholder="$t('salary_adjustment.year')"
                                                style="width: 100%;"
                                                type="number"
                                            />
                                        </a-col>
                                        <a-col :span="8">
                                            <a-input
                                                v-model:value="formData.month"
                                                :placeholder="$t('salary_adjustment.month')"
                                                style="width: 100%;"
                                                type="number"
                                            />
                                        </a-col>
                                        <a-col :span="8">
                                            <a-select
                                                v-model:value="formData.cut_off"
                                                style="width: 100%"
                                                :placeholder="$t('salary_adjustment.cutoff')"
                                            >
                                                <a-select-option value="A">A</a-select-option>
                                                <a-select-option value="B">B</a-select-option>
                                            </a-select>
                                        </a-col>
                                    </a-row>
                                </a-form-item>
                            </a-col>
                            
                        </template>

                        <!-- Date Range Options -->
                        <template v-if="formData.process_payment === 'date_range'">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('salary_adjustment.date_from')"
                                    name="date_from"
                                >
                                    <a-date-picker
                                        v-model:value="dateFromProxy"
                                        style="width: 100%"
                                        :format="'YYYY-MM-DD'"
                                        :placeholder="$t('salary_adjustment.date_from')"
                                    />
                                </a-form-item>
                            </a-col>

                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('salary_adjustment.date_to')"
                                    name="date_to"
                                >
                                    <a-date-picker
                                        v-model:value="dateToProxy"
                                        style="width: 100%"
                                        :format="'YYYY-MM-DD'"
                                        :placeholder="$t('salary_adjustment.date_to')"
                                    />
                                </a-form-item>
                            </a-col>
                        </template>

                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.amount')"
                                name="amount"
                            >
                                <a-input-number
                                    v-model:value="formData.amount"
                                    style="width: 100%"
                                    :min="0"
                                    :placeholder="$t('salary_adjustment.amount')"
                                />
                            </a-form-item>
                        </a-col>


                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salary_adjustment.type_taxable')"
                                name="type_taxable"
                            >
                                <a-select
                                    v-model:value="formData.type"
                                    style="width: 100%"
                                    :placeholder="$t('salary_adjustment.type_taxable')"
                                >
                                    <a-select-option value="NT">{{ $t('salary_adjustment.non_taxable') }}</a-select-option>
                                    <a-select-option value="T">{{ $t('salary_adjustment.taxable') }}</a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    
                </a-tab-pane>
                
            </a-tabs>
        </a-form>
        <template #footer>
            <a-space>
                <a-button type="primary" @click="onSubmit" :loading="loading">
                    <template #icon> <SaveOutlined /> </template>
                    {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
                </a-button>
                <a-button @click="onClose">
                    {{ $t("common.cancel") }}
                </a-button>
            </a-space>
        </template>
    </a-drawer>
</template>

<script>
import { LoadingOutlined, PlusOutlined, SaveOutlined } from "@ant-design/icons-vue";
import { computed, defineComponent, ref } from "vue";
import FormItemHeading from "../../../../common/components/common/typography/FormItemHeading.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";

import { useAuthStore } from "../../../../main/store/authStore";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "reFetchListOfData",
        "successMessage",
        'record'
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        FormItemHeading,
    },
    
    setup(props, { emit }) {
        const { permsArray, user, appSetting, dayjs } = common();
        const { addEditRequestAdmin, loading, rules, addEditActiveTab } = apiAdmin(
            "adjustment"
        );
        const roles = ref([]);
        const authStore = useAuthStore();
        const departments = ref([]);
        const designations = ref([]);
        const selectedVisibility = ref("manager");
        const locations = ref([]);
        const shifts = ref([]);
        const joiningDate = ref("");
        const showVisibilty = ref(false);
        const adjustedVisible = ref(false);
        const newData = ref({});
        
        const onSubmit = () => {
            var newFormData = {
                ...props.formData,
                visibility: selectedVisibility.value,
                ...newData.value,
            };

            console.log(newFormData.id)
            addEditRequestAdmin({
                id: newFormData.id,
                url: props.url,
                data: newFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                    authStore.updateAppAction();

                    if (user.value.xid == res.xid) {
                        authStore.updateUserAction();
                    }
                },
            });
        };
        



        const onClose = () => {
            rules.value = {};
            // showVisibilty.value = true;
            // console.log(showVisibilty.value)
            
            emit("closed");
        };

        // Proxy for date_from to handle dayjs conversion
        const dateFromProxy = computed({
            get() {
                return props.formData.date_from ? dayjs(props.formData.date_from) : null;
            },
            set(val) {
                props.formData.date_from = val ? val.format('YYYY-MM-DD') : null;
            }
        });

        const dateToProxy = computed({
            get() {
                return props.formData.date_to ? dayjs(props.formData.date_to) : null;
            },
            set(val) {
                props.formData.date_to = val ? val.format('YYYY-MM-DD') : null;
            }
        });

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,

            permsArray,
            appSetting,
            departments,
            designations,
            shifts,
            locations,
            joiningDate,
            showVisibilty,
            selectedVisibility,
            addEditActiveTab,
            adjustedVisible,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "65%",
            dateFromProxy,
            dateToProxy
        };
    },
});
</script>
