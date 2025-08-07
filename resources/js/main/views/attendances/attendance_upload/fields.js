import common from "@/common/composable/common";
import { ref } from "vue";
import { useI18n } from "vue-i18n";

const fields = () => {
    const url =
        "attendances_upload?fields=id,xid,user_id,x_user_id,user{id,xid,name,profile_image,profile_image_url}";
    const addEditUrl = "attendances_upload";
    const { t } = useI18n();
    const hashableColumns = ["user_id", "loc_id"];
    const { dayjs } = common();

    const initData = {
        user_id: undefined,
        date_from: dayjs().utc().format("YYYY-MM-DD"),
        date_to: dayjs().utc().format("YYYY-MM-DD"),
        shift_in: "",
        shift_out: "",
        shift_type: "",
        loc_id: undefined,
    };

    const columns = ref([
        {
            title: 'Employee',
            dataIndex: "user_id",
        },
        {
            title: 'Date From',
            dataIndex: "date",
        },
        {
            title:  'Date to',
            dataIndex: "date_to",
        },
        {
            title: 'In',
            dataIndex: "time_in",
        },
        {
            title: 'Out',
            dataIndex: "time_out",
        },
        {
            title: 'Shift Type',
            dataIndex: "schedule_type",
        },
        {
            title: 'Location/Detachment',
            dataIndex: "schedule_location_id ",
        },
        
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ]);

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name"),
        },
    ];

    return {
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        url,
    };
};

export default fields;