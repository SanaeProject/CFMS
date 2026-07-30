import { Footer } from "./Footer";

export function Page({children}: {children: React.ReactNode}){
    return (
        <div>
            <div className="container mt-5">
                {children}
            </div>
            <Footer />
        </div>
    )
}