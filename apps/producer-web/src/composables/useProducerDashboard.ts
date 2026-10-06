import { useCurrentSessionQuery } from "@/services/auth/auth.query";
import { useProducerDashboardQuery } from "@/services/dashboard/dashboard.query";
import { useRouter } from "vue-router";

export function useProducerDashboard() {
    const router = useRouter();
    const sessionQuery = useCurrentSessionQuery()
    

    const {
        data: producerDashBoardData,
        isLoading
     } = useProducerDashboardQuery()   

    return {
        isLoading,
        producerDashBoardData
    }
}